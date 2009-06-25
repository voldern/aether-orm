<?php

class AetherDatabasePgsqlDriver extends AetherDatabaseDriver {
    // Database connection link
    protected $link;
    protected $dbConfig;

    /**
     * Sets the config for the class
     *
     * @param array $config database configuration
     */
    public function __construct($config) {
        $this->dbConfig = $config;

        // ::log('debug', 'PgSQL database driver initialized');
    }

    public function connect() {
        // Check if link already exists
        if (is_resource($this->link))
            return $this->link;

        // Import conenction variables
        extract($this->dbConfig['connection']);

        // Persisten connections enabled?
        $connect = ($this->dbConfig['persistent'] === true) ?
            'pg_pconnect' : 'pg_connect';
        
        // Build the connection info
        $port = isset($port) ? 'port=\''.$port.'\'' : '';
        // if no host, connect with the socket
        $host = isset($host) ? 'host=\''.$host.'\' '.$port : '';

        $connectionString = "$host dbname='$database' user='$user' password='$pass'";

        // Make the connection and select the database
        if ($this->link = $connect($connectionString)) {
            if ($charset = $this->dbConfig['character_set'])
                echo $this->setCharset($charset);

            // Clear password after successful connect
            $this->dbConfig['connection']['pass'] = null;

            return $this->link;
        }

        return false;
    }

    public function query($sql) {
        // Only cache if it's turned on, and only cache if it's not a write statement
        if ($this->dbConfig['cache'] &&
            !preg_match('#\b(?:INSERT|UPDATE|SET)\b#i', $sql)) {
            $hash = $this->queryHash($sql);

            if (!isset($this->queryCache[$hash])) {
                // Set the cached object
                $this->queryCache[$hash] =
                    new AetherPgsqlResult(pg_query($this->link, $sql), $this->link,
                                          $this->dbConfig['object'], $sql);
            }
            else {
                // Rewind cached result
                $this->queryCache[$hash]->rewind();
            }

            return $this->queryCache[$hash];
        }

        // Suppress warning triggered when a database error occurs (e.g., a constraint violation)
        return new AetherPgsqlResult(@pg_query($this->link, $sql), $this->link,
                                     $this->dbConfig['object'], $sql);
    }

    public function setCharset($charset) {
        $this->query('SET client_encoding TO ' . pg_escape_string($this->link, $charset));
    }

    public function escapeTable($table) {
        if (!$this->dbConfig['escape'])
            return $table;

        return '"'.str_replace('.', '"."', $table).'"';
    }

    public function escapeColumn($column) {
        if (!$this->dbConfig['escape'])
            return $column;

        if ($column == '*')
            return $column;

        // This matches any functions we support to SELECT.
        if (preg_match('/(avg|count|sum|max|min)\(\s*(.*)\s*\)(\s*as\s*(.+)?)?/i',
                       $column, $matches)) {
            
            if (count($matches) == 3) {
                return $matches[1] . '(' . $this->escapeColumn($matches[2]) . ')';
            }
            elseif (count($matches) == 5) {
                return $matches[1]. '(' . $this->escapeColumn($matches[2]) .
                    ') AS ' .$this->escapeColumn($matches[2]);
            }
        }

        // This matches any modifiers we support to SELECT.
        if (!preg_match('/\b(?:all|distinct)\s/i', $column)) {
            if (stripos($column, ' AS ') !== false) {
                // Force 'AS' to uppercase
                $column = str_ireplace(' AS ', ' AS ', $column);

                // Runs escapeColumn on both sides of an AS statement
                $column = array_map(array($this, __FUNCTION__), explode(' AS ', $column));

                // Re-create the AS statement
                return implode(' AS ', $column);
            }

            return preg_replace('/[^.*]+/', '"$0"', $column);
        }

        $parts = explode(' ', $column);
        $column = '';

        for ($i = 0, $c = count($parts); $i < $c; $i++) {
            // The column is always last
            if ($i == ($c - 1)) {
                $column .= preg_replace('/[^.*]+/', '"$0"', $parts[$i]);
            }
            // otherwise, it's a modifier
            else {
                $column .= $parts[$i] . ' ';
            }
        }
        
        return $column;
    }

    public function regex($field, $match, $type, $numRegexs) {
        $prefix = ($numRegexs == 0) ? '' : $type;

        return $prefix . ' ' . $this->escapeColumn($field) .
            ' ~* \'' . $this->escapeStr($match) . '\'';
    }

    public function notregex($field, $match, $type, $numRegexs) {
        $prefix = $numRegexs == 0 ? '' : $type;

        return $prefix . ' ' . $this->escapeColumn($field) .
            ' !~* \'' . $this->escapeStr($match) . '\'';
    }

    public function limit($limit, $offset = 0) {
        return 'LIMIT ' . $limit . ' OFFSET ' . $offset;
    }

    public function compileSelect($database) {
        $sql = ($database['distinct'] == true) ? 'SELECT DISTINCT ' : 'SELECT ';
        $sql .= (count($database['select']) > 0) ? implode(', ', $database['select']) : '*';

        if (count($database['from']) > 0) {
            $sql .= "\nFROM ";
            $sql .= implode(', ', $database['from']);
        }

        if (count($database['join']) > 0) {
            foreach($database['join'] AS $join) {
                $sql .= "\n" . $join['type'] . 'JOIN ' .
                    implode(', ', $join['tables']) . ' ON ' .
                    $join['conditions'];
            }
        }

        if (count($database['where']) > 0) {
            $sql .= "\nWHERE ";
            $sql .= implode("\n", $database['where']);
        }

        if (count($database['groupby']) > 0) {
            $sql .= "\nGROUP BY ";
            $sql .= implode(', ', $database['groupby']);
        }

        if (count($database['having']) > 0) {
            $sql .= "\nHAVING ";
            $sql .= implode("\n", $database['having']);
        }

        if (count($database['orderby']) > 0) {
            $sql .= "\nORDER BY ";
            $sql .= implode(', ', $database['orderby']);
        }

        if (is_numeric($database['limit'])) {
            $sql .= "\n";
            $sql .= $this->limit($database['limit'], $database['offset']);
        }

        return $sql;
    }

    public function escapeStr($str)
    {
        if (!$this->dbConfig['escape'])
            return $str;

        if (!is_resource($this->link))
            $this->connect();

        return pg_escape_string($this->link, $str);
    }
    
    public function listTables() {
        $sql = "SELECT table_schema || '.' || table_name
                FROM information_schema.tables WHERE
                table_schema NOT IN ('pg_catalog', 'information_schema')";
        $result = $this->query($sql)->result(FALSE, PGSQL_ASSOC);

        $retval = array();
        foreach ($result as $row)
            $retval[] = current($row);

        return $retval;
    }

    public function showError() {
        return pg_last_error($this->link);
    }

    public function listFields($table)
    {
        $result = NULL;

        foreach ($this->fieldData($table) as $row) {
            // Make an associative array
            $result[$row->column_name] = $this->sqlType($row->data_type);

            if (!strncmp($row->column_default, 'nextval(', 8))
                $result[$row->column_name]['sequenced'] = TRUE;

            if ($row->is_nullable === 'YES')
                $result[$row->column_name]['null'] = TRUE;
        }

        if (!isset($result))
            throw new DatabaseException('table_not_found ' . $table);

        return $result;
    }

    public function fieldData($table)
    {
        // http://www.postgresql.org/docs/8.3/static/infoschema-columns.html
        $result = $this->query('
            SELECT column_name, column_default, is_nullable, data_type, udt_name,
                character_maximum_length, numeric_precision, numeric_precision_radix, numeric_scale
            FROM information_schema.columns
            WHERE table_name = \''. $this->escapeStr($table) .'\'
            ORDER BY ordinal_position
        ');

        return $result->resultArray(TRUE);
    }
}

class AetherPgsqlResult extends AetherDatabaseResult {
    // Data fetching types
    protected $fetchType  = 'pgsql_fetch_object';
    protected $returnType = PGSQL_ASSOC;

    /**
     * Sets up the result variables.
     *
     * @param resource $result query result
     * @param resource $link database link
     * @param boolean $object return objects or arrays
     * @param string $sql SQL query that was run
     */
    public function __construct($result, $link, $object = TRUE, $sql) {
        $this->link = $link;
        $this->result = $result;

        // If the query is a resource, it was a SELECT, SHOW, DESCRIBE, EXPLAIN query
        if (is_resource($result)) {
            // Its an DELETE, INSERT, REPLACE, or UPDATE query
            if (preg_match('/^(?:delete|insert|replace|update)\b/iD',
                           trim($sql), $matches)) {
                $this->insertId  = (strtolower($matches[0]) == 'insert') ? $this->insertId() : FALSE;
                $this->totalRows = pg_affected_rows($this->result);
            }
            else {
                $this->currentRow = 0;
                $this->totalRows  = pg_num_rows($this->result);
                $this->fetchType = ($object === TRUE) ? 'pg_fetch_object' : 'pg_fetch_array';
            }
        }
        else {
            throw new DatabaseException('pgsql error: ' . pg_last_error() .
                                        ' - ' . $sql);
        }

        // Set result type
        $this->result($object);

        // Store the SQL
        $this->sql = $sql;
    }

    /**
     * Magic __destruct function, frees the result.
     */
    public function __destruct() {
        if (is_resource($this->result))
            pg_free_result($this->result);
    }

    public function result($object = true, $type = PGSQL_ASSOC) {
        $this->fetchType = ((bool)$object) ? 'pg_fetch_object' : 'pg_fetch_array';

        // This check has to be outside the previous statement, because we do not
        // know the state of fetch_type when $object = NULL
        // NOTE - The class set by $type must be defined before fetching the result,
        // autoloading is disabled to save a lot of stupid overhead.
        if ($this->fetchType == 'pg_fetch_object') {
            $this->returnType = (is_string($type) && Config::autoLoad($type)) ?
                $type : 'stdClass';
        }
        else {
            $this->returnType = $type;
        }

        return $this;
    }

    public function asArray($object = null, $type = PGSQL_ASSOC) {
        return $this->resultArray($object, $type);
    }

    public function resultArray($object = null, $type = PGSQL_ASSOC) {
        $rows = array();

        if (is_string($object)) {
            $fetch = $object;
        }
        elseif (is_bool($object)) {
            if ($object === true) {
                $fetch = 'pg_fetch_object';

                // NOTE - The class set by $type must be defined before fetching the result,
                // autoloading is disabled to save a lot of stupid overhead.
                $type = (is_string($type) && Config::autoLoad($type)) ?
                    $type : 'stdClass';
            }
            else {
                $fetch = 'pg_fetch_array';
            }
        }
        else {
            // Use the default config values
            $fetch = $this->fetchType;

            if ($fetch == 'pg_fetch_object') {
                $type = (is_string($type) && Config::autoLoad($type)) ? $type : 'stdClass';
            }
        }

        if ($this->totalRows) {
            pg_result_seek($this->result, 0);

            while ($row = $fetch($this->result, NULL, $type))
                $rows[] = $row;
        }

        return $rows;
    }

    public function insertId() {
        if ($this->insertId === NULL) {
            $query = 'SELECT LASTVAL() AS insert_id';

            // Disable error reporting for this, just to silence errors on
            // tables that have no serial column.
            $ER = error_reporting(0);

            $result = pg_query($this->link, $query);
            $insertId = pg_fetch_array($result, NULL, PGSQL_ASSOC);

            $this->insertId = $insertId['insert_id'];

            // Reset error reporting
            error_reporting($ER);
        }

        return $this->insertId;
    }

    public function seek($offset) {
        if ($this->offsetExists($offset) && pg_result_seek($this->result, $offset)) {
            // Set the current row to the offset
            $this->currentRow = $offset;

            return true;
        }

        return false;
    }

    public function listFields() {
        $fieldNames = array();

        $fields = pg_num_fields($this->result);
        for ($i = 0; $i < $fields; ++$i)
            $fieldNames[] = pg_field_name($this->result, $i);

        return $fieldNames;
    }

    /**
     * ArrayAccess: offsetGet
     */
    public function offsetGet($offset) {
        if (!$this->seek($offset))
            return false;

        // Return the row by calling the defined fetching callback
        $fetch = $this->fetchType;
        return $fetch($this->result, NULL, $this->returnType);
    }
}

/**
 * PostgreSQL Prepared Statement (experimental)
 */
class PgsqlStatement {

    protected $link = NULL;
    protected $stmt;

    public function __construct($sql, $link) {
        $this->link = $link;

        $this->stmt = $this->link->prepare($sql);

        return $this;
    }

    public function __destruct() {
        $this->stmt->close();
    }

    // Sets the bind parameters
    public function bindParams() {
        $argv = func_get_args();
        return $this;
    }

    // sets the statement values to the bound parameters
    public function setVals() {
        return $this;
    }

    // Runs the statement
    public function execute() {
        return $this;
    }
}
