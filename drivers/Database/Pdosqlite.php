<?php
/**
 *
 *
 * @package Database
 * @author Espen Volden
 */
class AetherDatabasePdosqliteDriver extends AetherDatabaseDriver {

    // Database connection link
    protected $link;
    protected $dbConfig;

    /*
     * Constructor: __construct
     *  Sets up the config for the class.
     *
     * @param string $config database config
     */
    public function __construct($config) {
        $this->dbConfig = $config;

        // ::log('debug', 'PDO:Sqlite Database Driver Initialized');
    }

    public function connect() {
        // Import the connect variables
        extract($this->dbConfig['connection']);

        try {
            $this->link = new PDO('sqlite:'.$socket.$database, $user, $pass,
                                  array(PDO::ATTR_PERSISTENT => $this->dbConfig['persistent']));

            $this->link->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
            //$this->link->query('PRAGMA count_changes=1;');

            if ($charset = $this->dbConfig['character_set'])
                $this->setCharset($charset);
        }
        catch (PDOException $e) {
            throw new DatabaseException('database error: ' . $e->getMessage());
        }

        // Clear password after successful connect
        $this->dbConfig['connection']['pass'] = NULL;

        return $this->link;
    }

    public function query($sql) {
        try {
            $sth = $this->link->prepare($sql);
        }
        catch (PDOException $e) {
            throw new DatabaseException('database error: ' . $e->getMessage());
        }
        
        return new AetherPdosqliteResult($sth, $this->link,
                                         $this->dbConfig['object'], $sql);
    }

    public function setCharset($charset) {
        $this->link->query('PRAGMA encoding = '.$this->escapeStr($charset));
    }

    public function escapeTable($table) {
        if (!$this->dbConfig['escape'])
            return $table;

        return '`'.str_replace('.', '`.`', $table).'`';
    }

    public function escapeColumn($column) {
        if (!$this->dbConfig['escape'])
            return $column;

        if ($column == '*')
            return $column;

        // This matches any functions we support to SELECT.
        if (preg_match('/(avg|count|sum|max|min)\(\s*(.*)\s*\)(\s*as\s*(.+)?)?/i',
                       $column, $matches)) {
            if (count($matches) == 3)
                return $matches[1].'('.$this->escapeColumn($matches[2]).')';
            else if ( count($matches) == 5)
                return $matches[1] .'(' . $this->escapeColumn($matches[2]) .
                    ') AS ' . $this->escapeColumn($matches[2]);
        }

        // This matches any modifiers we support to SELECT.
        $regex = '/\b(?:rand|all|distinct(?:row)?|high_priority|sql_(?:' .
            'small_result|b(?:ig_result|uffer_result)|no_cache|ca(?:che|' .
            'lc_found_rows)))\s/i';
        if (!preg_match($regex, $column)) {
            if (stripos($column, ' AS ') !== false) {
                // Force 'AS' to uppercase
                $column = str_ireplace(' AS ', ' AS ', $column);

                // Runs escape_column on both sides of an AS statement
                $column = array_map(array($this, __FUNCTION__), explode(' AS ', $column));

                // Re-create the AS statement
                return implode(' AS ', $column);
            }

            return preg_replace('/[^.*]+/', '`$0`', $column);
        }

        $parts = explode(' ', $column);
        $column = '';

        for ($i = 0, $c = count($parts); $i < $c; $i++) {
            // The column is always last
            if ($i == ($c - 1))
                $column .= preg_replace('/[^.*]+/', '`$0`', $parts[$i]);
            else // otherwise, it's a modifier
                $column .= $parts[$i].' ';
        }
        return $column;
    }

    public function limit($limit, $offset = 0) {
        return 'LIMIT '.$offset.', '.$limit;
    }

    public function compileSelect($database) {
        $sql = ($database['distinct'] == TRUE) ? 'SELECT DISTINCT ' : 'SELECT ';
        $sql .= (count($database['select']) > 0) ? implode(', ', $database['select']) : '*';

        if (count($database['from']) > 0) {
            $sql .= "\nFROM ";
            $sql .= implode(', ', $database['from']);
        }

        if (count($database['join']) > 0) {
            foreach($database['join'] AS $join) {
                $sql .= "\n" . $join['type'] . 'JOIN ' .
                    implode(', ', $join['tables']) . ' ON ' . $join['conditions'];
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

    public function escapeStr($str) {
        if (!$this->dbConfig['escape'])
            return $str;

        if (function_exists('sqlite_escape_string'))
            $res = sqlite_escape_string($str);
        else
            $res = str_replace("'", "''", $str);
        
        return $res;
    }

    public function listTables() {
        $sql = "SELECT `name` FROM `sqlite_master` WHERE `type`='table' ORDER BY `name`;";
        try {
            $result = $this->query($sql)->result(FALSE, PDO::FETCH_ASSOC);
            $tables = array();
            foreach ($result as $row)
                $tables[] = current($row);
        }
        catch (PDOException $e) {
            throw new DatabaseException('database error: ' . $e->getMessage());
        }
        
        return $tables;
    }

    public function showError() {
        $err = $this->link->errorInfo();
        return isset($err[2]) ? $err[2] : 'Unknown error!';
    }

    public function listFields($table, $query = FALSE) {
        static $tables;
        if (is_object($query)) {
            if (empty($tables[$table])) {
                $tables[$table] = array();

                foreach ($query->result() as $row)
                    $tables[$table][] = $row->name;
            }

            return $tables[$table];
        }
        else {
            $result = $this->link->query('PRAGMA table_info(' .
                                         $this->escapeTable($table) .')');

            foreach ($result as $row)
                $tables[$table][$row['name']] = $this->sqlType($row['type']);

            return $tables[$table];
        }
    }

    public function fieldData($table) {
        // ::log('error', 'This method is under developing');
    }
    
    /**
     * Version number query string
     *
     * @access  public
     * @return  string
     */
    function version() {
        return $this->link->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
    }

}

/*
 * PDO-sqlite Result
 */
class AetherPdosqliteResult extends AetherDatabaseResult {

    // Data fetching types
    protected $fetchType  = PDO::FETCH_OBJ;
    protected $returnType = PDO::FETCH_ASSOC;

    /**
     * Sets up the result variables.
     *
     * @param  resource  query result
     * @param  resource  database link
     * @param  boolean   return objects or arrays
     * @param  string    SQL query that was run
     */
    public function __construct($result, $link, $object = true, $sql) {
        if (is_object($result) || $result = $link->prepare($sql)) {
            // run the query. Return true if success, false otherwise
            if(!$result->execute()) {
                // Throw Kohana Exception with error message. See PDOStatement errorInfo() method
                $arrInfos = $result->errorInfo();
                throw new DatabaseException('database error: ' . $arrInfos[2]);
            }

            if (preg_match('/^SELECT|PRAGMA|EXPLAIN/i', $sql)) {
                $this->result = $result;
                $this->currentRow = 0;

                $this->totalRows = $this->sqliteRowCount();

                $this->fetchType = ($object === TRUE) ?
                    PDO::FETCH_OBJ : PDO::FETCH_ASSOC;
            }
            elseif (preg_match('/^DELETE|INSERT|UPDATE/i', $sql)) {
                $this->insertId  = $link->lastInsertId();

                $this->totalRows = $result->rowCount();
            }
        }
        else {
            // SQL error
            throw new DatabaseException('database error: ' . $link->errorInfo() .
                                        ' - '.$sql);
        }

        // Set result type
        $this->result($object);

        // Store the SQL
        $this->sql = $sql;
    }

    private function sqliteRowCount() {
        $count = 0;
        while ($this->result->fetch())
            $count++;

        // The query must be re-fetched now.
        $this->result->execute();

        return $count;
    }

    /*
     * Destructor: __destruct
     *  Magic __destruct function, frees the result.
     */
    public function __destruct() {
        if (is_object($this->result)) {
            $this->result->closeCursor();
            $this->result = NULL;
        }
    }

    public function result($object = TRUE, $type = PDO::FETCH_BOTH) {
        $this->fetchType = (bool) $object ? PDO::FETCH_OBJ : PDO::FETCH_BOTH;

        if ($this->fetchType == PDO::FETCH_OBJ)
            $this->returnType = (is_string($type) && Config::autoLoad($type)) ?
                $type : 'stdClass';
        else
            $this->returnType = $type;

        return $this;
    }

    public function asArray($object = NULL, $type = PDO::FETCH_ASSOC) {
        return $this->resultArray($object, $type);
    }

    public function resultArray($object = NULL, $type = PDO::FETCH_ASSOC) {
        $rows = array();

        if (is_string($object)) {
            $fetch = $object;
        }
        elseif (is_bool($object)) {
            if ($object === true) {
                $fetch = PDO::FETCH_OBJ;

                // NOTE - The class set by $type must be defined before fetching the result,
                // autoloading is disabled to save a lot of stupid overhead.
                $type = (is_string($type) && Config::autoLoad($type)) ?
                    $type : 'stdClass';
            }
            else {
                $fetch = PDO::FETCH_OBJ;
            }
        }
        else {
            // Use the default config values
            $fetch = $this->fetchType;

            if ($fetch == PDO::FETCH_OBJ) 
                $type = (is_string($type) && Config::autoLoad($type)) ?
                    $type : 'stdClass';
        }
        
        try {
            while ($row = $this->result->fetch($fetch))
                $rows[] = $row;
        }
        catch(PDOException $e) {
            throw new DatabaseException('database error: ' . $e->getMessage());
            return false;
        }
        
        return $rows;
    }

    public function listFields() {
        $fieldNames = array();
        for ($i = 0, $max = $this->result->columnCount(); $i < $max; $i++) {
            $info = $this->result->getColumnMeta($i);
            $fieldNames[] = $info['name'];
        }
        
        return $fieldNames;
    }

    public function seek($offset) {
        // To request a scrollable cursor for your PDOStatement object, you must
        // set the PDO::ATTR_CURSOR attribute to PDO::CURSOR_SCROLL when you
        // prepare the statement.
        // ::log('error', get_class($this).' does not support scrollable cursors, '.__FUNCTION__.' call ignored');

        return false;
    }

    public function offsetGet($offset) {
        try {
            return $this->result->fetch($this->fetchType, PDO::FETCH_ORI_ABS, $offset);
        }
        catch(PDOException $e) {
            throw new DatabaseException('database error: ' . $e->getMessage());
        }
    }

    public function rewind() {
        // Same problem that seek() has, see above.
        return $this->seek(0);
    }

}