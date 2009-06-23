<?php

class DatabaseMysqliDriver extends DatabaseMysqlDriver {

    // Database connection link
    protected $link;
    protected $dbConfig;
    protected $statements = array();

    /**
     * Sets the config for the class.
     *
     * @param array $config database configuration
     */
    public function __construct($config) {
        $this->dbConfig = $config;

        // ::log('debug', 'MySQLi Database Driver Initialized');
    }

    /**
     * Closes the database connection.
     */
    public function __destruct() {
        if (is_object($this->link))
            $this->link->close();
    }

    public function connect() {
        // Check if link already exists
        if (is_object($this->link))
            return $this->link;

        // Import the connect variables
        extract($this->dbConfig['connection']);

        // Build the connection info
        $host = isset($host) ? $host : $socket;

        // Make the connection and select the database
        if ($this->link = new mysqli($host, $user, $pass, $database, $port)) {
            if ($charset = $this->dbConfig['character_set']) {
                $this->setCharset($charset);
            }

            // Clear password after successful connect
            $this->dbConfig['connection']['pass'] = NULL;

            return $this->link;
        }

        return false;
    }

    public function query($sql) {
        // Only cache if it's turned on, and only cache if it's not a write statement
        if ($this->dbConfig['cache'] &&
            !preg_match('#\b(?:INSERT|UPDATE|REPLACE|SET|DELETE|TRUNCATE)\b#i', $sql)) {

            $hash = $this->queryHash($sql);

            if (!isset($this->queryCache[$hash])) {
                // Set the cached object
                $this->queryCache[$hash] =
                    new MysqliResult($this->link, $this->dbConfig['object'], $sql);
            }
            else {
                // Rewind cached result
                $this->queryCache[$hash]->rewind();
            }

            // Return the cached query
            return $this->queryCache[$hash];
        }

        return new MysqliResult($this->link, $this->dbConfig['object'], $sql);
    }

    public function setCharset($charset) {
        if ($this->link->set_charset($charset) === false)
            throw new DatabaseException('database error: ' . $this->showError());
    }

    public function escapeStr($str) {
        if (!$this->dbConfig['escape'])
            return $str;

        if (!is_object($this->link))
            $this->connect();

        return $this->link->real_escape_string($str);
    }

    public function showError() {
        return $this->link->error;
    }

} // End Database_Mysqli_Driver Class


class MysqliResult extends DatabaseResult {

    // Database connection
    protected $link;

    // Data fetching types
    protected $fetchType  = 'mysqli_fetch_object';
    protected $returnType = MYSQLI_ASSOC;

    /**
     * Sets up the result variables.
     *
     * @param object $link database link
     * @param boolean $object return objects or arrays
     * @param string $sql SQL query that was run
     */
    public function __construct($link, $object = true, $sql) {
        $this->link = $link;

        if (!$this->link->multi_query($sql)) {
            // SQL error
            throw new DatabaseException('database error: ' . $this->link->error .
                                        ' - ' . $sql);
        }
        else {
            $this->result = $this->link->store_result();

            // If the query is an object, it was a SELECT, SHOW, DESCRIBE, EXPLAIN query
            if (is_object($this->result)) {
                $this->currentRow = 0;
                $this->totalRows  = $this->result->num_rows;
                $this->fetchType = ($object === true) ?
                    'fetch_object' : 'fetch_array';
            }
            elseif ($this->link->error) {
                // SQL error
                throw new DatabaseException('database error: ' .
                                            $this->link->error . ' - ' . $sql);
            }
            else {
                // Its an DELETE, INSERT, REPLACE, or UPDATE query
                $this->insertId  = $this->link->insert_id;
                $this->totalRows = $this->link->affected_rows;
            }
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
        if (is_object($this->result)) {
            $this->result->free_result();

            // this is kinda useless, but needs to be done to avoid the "Commands out of sync; you
            // can't run this command now" error. Basically, we get all results after the first one
            // (the one we actually need) and free them.
            if (is_resource($this->link) && $this->link->more_results()) {
                do {
                    if ($result = $this->link->store_result())
                        $result->free_result();
                } while ($this->link->next_result());
            }
        }
    }

    public function result($object = true, $type = MYSQLI_ASSOC) {
        $this->fetchType = ((bool)$object) ? 'fetch_object' : 'fetch_array';

        // This check has to be outside the previous statement, because we do not
        // know the state of fetch_type when $object = NULL
        // NOTE - The class set by $type must be defined before fetching the result,
        // autoloading is disabled to save a lot of stupid overhead.
        if ($this->fetchType == 'fetch_object') {
            $this->returnType = (is_string($type) && Config::auto_load($type)) ?
                $type : 'stdClass';
        }
        else {
            $this->returnType = $type;
        }

        return $this;
    }

    public function asArray($object = NULL, $type = MYSQLI_ASSOC) {
        return $this->resultArray($object, $type);
    }

    public function resultArray($object = NULL, $type = MYSQLI_ASSOC) {
        $rows = array();

        if (is_string($object)) {
            $fetch = $object;
        }
        elseif (is_bool($object)) {
            if ($object === true) {
                $fetch = 'fetch_object';

                // NOTE - The class set by $type must be defined before fetching the result,
                // autoloading is disabled to save a lot of stupid overhead.
                $type = (is_string($type) && Config::auto_load($type)) ?
                    $type : 'stdClass';
            }
            else {
                $fetch = 'fetch_array';
            }
        }
        else {
            // Use the default config values
            $fetch = $this->fetchType;

            if ($fetch == 'fetch_object') {
                $type = (is_string($type) && Config::auto_load($type)) ?
                    $type : 'stdClass';
            }
        }

        if ($this->result->num_rows) {
            // Reset the pointer location to make sure things work properly
            $this->result->data_seek(0);

            while ($row = $this->result->$fetch($type))
                $rows[] = $row;
        }

        return isset($rows) ? $rows : array();
    }

    public function listFields() {
        $fieldNames = array();
        while ($field = $this->result->fetch_field())
            $fieldNames[] = $field->name;

        return $fieldNames;
    }

    public function seek($offset) {
        if ($this->offsetExists($offset) && $this->result->data_seek($offset)) {
            // Set the current row to the offset
            $this->currentRow = $offset;

            return true;
        }

        return false;
    }

    public function offsetGet($offset) {
        if (!$this->seek($offset))
            return false;

        // Return the row
        $fetch = $this->fetchType;
        return $this->result->$fetch($this->returnType);
    }

}

class MysqliStatement {

    protected $link = NULL;
    protected $stmt;
    protected $varNames = array();
    protected $varValues = array();

    public function __construct($sql, $link) {
        $this->link = $link;

        $this->stmt = $this->link->prepare($sql);

        return $this;
    }

    public function __destruct() {
        $this->stmt->close();
    }

    // Sets the bind parameters
    public function bindParams($paramTypes, $params) { 
        $this->varNames = array_keys($params);
        $this->varValues = array_values($params);
        call_user_func_array(array($this->stmt, 'bind_param'),
                             array_merge($paramTypes, $varNames));

        return $this;
    }

    public function bindResult($params) {
        call_user_func_array(array($this->stmt, 'bind_result'), $params);
    }

    // Runs the statement
    public function execute() {
        foreach ($this->varNames as $key => $name)
            $$name = $this->varValues[$key];
        
        $this->stmt->execute();
        return $this->stmt;
    }
}
