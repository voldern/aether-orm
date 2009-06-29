<?php
/**
 *
 * @package Database
 * @author Espen Volden
 */
abstract class AetherDatabaseDriver {
    protected $queryCache;

    abstract public function connect();

    abstract public function query($sql);
    /**
     * Builds a DELETE query.
     *
     * @param   string  table name
     * @param   array   where clause
     * @return  string
     */
    public function delete($table, $where) {
        return 'DELETE FROM '.$this->escapeTable($table).' WHERE '.implode(' ', $where);
    }

    /**
     * Builds an UPDATE query.
     *
     * @param string $table table name
     * @param array $values key => value pairs
     * @param array $where where clause
     * @return string
     */
    public function update($table, $values, $where) {
        foreach ($values as $key => $val) {
            $valstr[] = $this->escapeColumn($key).' = '.$val;
        }
        
        return 'UPDATE ' . $this->escapeTable($table) . ' SET ' .
            implode(', ', $valstr) . ' WHERE ' . implode(' ',$where);
    }

    /**
     * Set the charset using 'SET NAMES <charset>'.
     *
     * @param string $charset character set to use
     */
    public function setCharset($charset) {
        throw new DatabaseException('not_implemented ' . __FUNCTION__);
    }

    /**
     * Wrap the tablename in backticks, has support for: table.field syntax.
     *
     * @param string $table table name
     * @return string
     */
    abstract public function escapeTable($table);

    /**
     * Escape a column/field name, has support for special commands.
     *
     * @param string $column column name
     * @return string
     */
    abstract public function escapeColumn($column);

    /**
     * Builds a WHERE portion of a query.
     *
     * @param mixed $key key
     * @param string $value value
     * @param string $type type
     * @param int $numWheres number of where clauses
     * @param boolean $quote escape the value
     * @return string
     */
    public function where($key, $value, $type, $numWheres, $quote) {
        $prefix = ($numWheres == 0) ? '' : $type;

        if ($quote === -1) {
            $value = '';
        }
        else {
            if ($value === null) {
                if (!$this->hasOperator($key))
                    $key .= ' IS';

                $value = ' NULL';
            }
            elseif (is_bool($value)) {
                if (!$this->hasOperator($key)) {
                    $key .= ' =';
                }

                $value = ($value == true) ? ' 1' : ' 0';
            }
            else {
                if (!$this->hasOperator($key) && !empty($key)) {
                    $key = $this->escapeColumn($key) . ' =';
                }
                else {
                    preg_match('/^(.+?)([<>!=]+|\bIS(?:\s+NULL))\s*$/i', $key, $matches);
                    if (isset($matches[1]) && isset($matches[2])) {
                        $key = $this->escapeColumn(trim($matches[1])) . ' ' .
                            trim($matches[2]);
                    }
                }

                $value = ' '. (($quote == true) ? $this->escape($value) : $value);
            }
        }

        return $prefix . $key . $value;
    }

    /**
     * Builds a LIKE portion of a query.
     *
     * @param mixed $field field name
     * @param string $match value to match with field
     * @param boolean $auto add wildcards before and after the match
     * @param string $type clause type (AND or OR)
     * @param int $numLikes number of likes
     * @return string
     */
    public function like($field, $match, $auto, $type, $numLikes) {
        $prefix = ($numLikes == 0) ? '' : $type;

        $match = $this->escapeStr($match);

        if ($auto === true) {
            // Add the start and end quotes
            $match = '%'.str_replace('%', '\\%', $match).'%';
        }

        return $prefix . ' ' . $this->escapeColumn($field) . " LIKE '$match'";
    }

    /**
     * Builds a NOT LIKE portion of a query.
     *
     * @param mixed $field field name
     * @param string $match value to match with field
     * @param boolean $auto add wildcards before and after the match
     * @param string $type clause type (AND or OR)
     * @param int $numLikes number of likes
     * @return string
     */
    public function notlike($field, $match, $auto, $type, $numLikes) {
        $prefix = ($num_likes == 0) ? '' : $type;

        $match = $this->escapeStr($match);

        if ($auto === true) {
            // Add the start and end quotes
            $match = '%'.$match.'%';
        }

        return $prefix . ' ' . $this->escapeColumn($field) . " NOT LIKE '$match'";
    }

    /**
     * Builds a REGEX portion of a query.
     *
     * @param string $field field name
     * @param string $match value to match with field
     * @param string $type clause type (AND or OR)
     * @param integer $numRegexs number of regexes
     * @return string
     */
    public function regex($field, $match, $type, $numRegexs) {
        throw new DatabaseException('not_implemented ' . __FUNCTION__);
    }

    /**
     * Builds a NOT REGEX portion of a query.
     *
     * @param string $field field name
     * @param string $match value to match with field
     * @param string $type clause type (AND or OR)
     * @param integer $numRegexs number of regexes
     * @return string
     */
    public function notregex($field, $match, $type, $numRegexs) {
        throw new DatabaseException('not_implemented ' . __FUNCTION__);
    }

    /**
     * Builds an INSERT query.
     *
     * @param string $table table name
     * @param array $keys keys
     * @param array $values  values
     * @return string
     */
    public function insert($table, $keys, $values) {
        // Escape the column names
        foreach ($keys as $key => $value) {
            $keys[$key] = $this->escapeColumn($value);
        }
        
        return 'INSERT INTO ' . $this->escapeTable($table) . ' (' .
            implode(', ', $keys) . ') VALUES (' . implode(', ', $values) . ')';
    }

    /**
     * Builds a MERGE portion of a query.
     *
     * @param string $table table name
     * @param array $keys keys
     * @param array $values values
     * @return string
     */
    public function merge($table, $keys, $values) {
        throw new DatabaseException('not_implemented ' . __FUNCTION__);
    }

    /**
     * Builds a LIMIT portion of a query.
     *
     * @param integer $limit limit
     * @param integer $offset defaults to 0
     * @return string
     */
    abstract public function limit($limit, $offset = 0);

    /**
     * Creates a prepared statement.
     *
     * @param   string  SQL query
     * @return  DatabaseStmt
     */
    public function stmtPrepare($sql = '') {
        throw new DatabaseException('not_implemented ' . __FUNCTION__);
    }

    /**
     *  Compiles the SELECT statement.
     *  Generates a query string based on which functions were used.
     *  Should not be called directly, the get() function calls it.
     *
     * @param array $database select query values
     * @return string
     */
    abstract public function compileSelect($database);

    /**
     * Determines if the string has an arithmetic operator in it.
     *
     * @param string $str string to check
     * @return boolean
     */
    public function hasOperator($str) {
        return (bool)preg_match('/[<>!=]|\sIS(?:\s+NOT\s+)?\b|BETWEEN/i', trim($str));
    }

    /**
     * Escapes any input value.
     *
     * @param mixed $value value to escape
     * @return string
     */
    public function escape($value) {
        if (!$this->dbConfig['escape'])
            return $value;

        switch (gettype($value)) {
        case 'string':
            $value = '\'' . $this->escapeStr($value) . '\'';
            break;
        case 'boolean':
            $value = (int)$value;
            break;
        case 'double':
            // Convert to non-locale aware float to prevent possible commas
            $value = sprintf('%F', $value);
            break;
        default:
            $value = ($value === NULL) ? 'NULL' : $value;
            break;
        }

        return (string)$value;
    }

    /**
     * Escapes a string for a query.
     *
     * @param mixed $str value to escape
     * @return string
     */
    abstract public function escapeStr($str);

    /**
     * Lists all tables in the database.
     *
     * @return array
     */
    abstract public function listTables();

    /**
     * Lists all fields in a table.
     *
     * @param string $table table name
     * @return array
     */
    abstract function listFields($table);

    /**
     * Returns the last database error.
     *
     * @return string
     */
    abstract public function showError();

    /**
     * Returns field data about a table.
     *
     * @param string $table table name
     * @return array
     */
    abstract public function fieldData($table);

    /**
     * Fetches SQL type information about a field, in a generic format.
     *
     * @param string $str field datatype
     * @return array
     */
    protected function sqlType($str) {
        static $sqlTypes;

        if ($sqlTypes === NULL) {
            // Load SQL data types
            $sqlTypes = AetherDatabaseConfig::retrieve('sql_types');
        }

        $str = strtolower(trim($str));

        if (($open  = strpos($str, '(')) !== false) {
            // Find closing bracket
            $close = strpos($str, ')', $open) - 1;

            // Find the type without the size
            $type = substr($str, 0, $open);
        }
        else {
            // No length
            $type = $str;
        }

        if (empty($sqlTypes[$type])) {
            exit('Unknown field type: ' . $type . '. ' .
                 'Please report this to: drift@hardware.no!');
        }

        // Fetch the field definition
        $field = $sqlTypes[$type];

        switch ($field['type']) {
        case 'string':
        case 'float':
            if (isset($close)) {
                // Add the length to the field info
                $field['length'] = substr($str, $open + 1, $close - $open);
            }
            break;
        case 'int':
            // Add unsigned value
            $field['unsigned'] = (strpos($str, 'unsigned') !== false);
            break;
        }

        return $field;
    }

    /**
     * Clears the internal query cache.
     *
     * @param string $sql SQL query
     */
    public function clearCache($sql = NULL) {
        if (empty($sql)) {
            $this->queryCache = array();
        }
        else {
            unset($this->queryCache[$this->queryHash($sql)]);
        }

        // ::log('debug', 'Database cache cleared: '.get_class($this));
    }

    /**
     * Creates a hash for an SQL query string. Replaces newlines with spaces,
     * trims, and hashes.
     *
     * @param string $sql SQL query
     * @return string
     */
    protected function queryHash($sql) {
        return sha1(str_replace("\n", ' ', trim($sql)));
    }
}

abstract class AetherDatabaseResult implements ArrayAccess, Iterator, Countable {

    // Result resource, insert id, and SQL
    protected $result;
    protected $insertId;
    protected $sql;

    // Current and total rows
    protected $currentRow = 0;
    protected $totalRows  = 0;

    // Fetch function and return type
    protected $fetchType;
    protected $returnType;

    /**
     * Returns the SQL used to fetch the result.
     *
     * @return string
     */
    public function sql() {
        return $this->sql;
    }

    /**
     * Returns the insert id from the result.
     *
     * @return  mixed
     */
    public function insertId() {
        return $this->insertId;
    }

    /**
     * Prepares the query result.
     *
     * @param boolean $object return rows as objects
     * @param mixed $type type
     * @return AetherDatabaseResult
     */
    abstract function result($object = TRUE, $type = FALSE);

    /**
     * Builds an array of query results.
     *
     * @param boolean $object return rows as objects
     * @param mixed $type type
     * @return array
     */
    abstract function resultArray($object = NULL, $type = FALSE);

    /**
     * Gets the fields of an already run query.
     *
     * @return array
     */
    abstract public function listFields();

    /**
     * Seek to an offset in the results.
     *
     * @param int $offset
     * @return  boolean
     */
    abstract public function seek($offset);

    /**
     * Countable: count
     */
    public function count() {
        return $this->totalRows;
    }

    /**
     * ArrayAccess: offsetExists
     */
    public function offsetExists($offset) {
        if ($this->totalRows > 0) {
            $min = 0;
            $max = $this->totalRows - 1;

            if ($offset < $min || $offset > $max)
                return false;
            else
                return true;
        }

        return false;
    }

    /**
     * ArrayAccess: offsetGet
     */
    public function offsetGet($offset) {
        if (!$this->seek($offset))
            return false;

        // Return the row by calling the defined fetching callback
        return call_user_func($this->fetchType, $this->result, $this->returnType);
    }

    /**
     * ArrayAccess: offsetSet
     *
     * @throws  DatabaseException
     */
    final public function offsetSet($offset, $value) {
        throw new DatabaseException('result_read_only');
    }

    /**
     * ArrayAccess: offsetUnset
     *
     * @throws  DatabaseException
     */
    final public function offsetUnset($offset) {
        throw new DatabaseException('result_read_only');
    }

    /**
     * Iterator: current
     */
    public function current() {
        return $this->offsetGet($this->currentRow);
    }

    /**
     * Iterator: key
     */
    public function key() {
        return $this->currentRow;
    }

    /**
     * Iterator: next
     */
    public function next() {
        ++$this->currentRow;
        return $this;
    }

    /**
     * Iterator: prev
     */
    public function prev() {
        --$this->currentRow;
        return $this;
    }

    /**
     * Iterator: rewind
     */
    public function rewind() {
        $this->currentRow = 0;
        return $this;
    }

    /**
     * Iterator: valid
     */
    public function valid() {
        return $this->offsetExists($this->currentRow);
    }

}