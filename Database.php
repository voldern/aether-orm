<?php
/**
 *
 * @package Database
 * @author Espen Volden
 */
class AetherDatabase {
    // AetherDatabase instances
    public static $instances = array();

    // Global benchmark
    public static $benchmarks = array();

    // Configuration
    protected $config = array(
        'benchmark'     => true,
        'persistent'    => false,
        'connection'    => '',
        'character_set' => 'utf8',
        'table_prefix'  => '',
        'object'        => true,
        'cache'         => false,
        'escape'        => true,
        );

    // AetherDatabase driver object
    protected $driver;
    protected $link;

    // Un-compiled parts of the SQL query
    protected $select     = array();
    protected $set        = array();
    protected $from       = array();
    protected $join       = array();
    protected $where      = array();
    protected $orderby    = array();
    protected $order      = array();
    protected $groupby    = array();
    protected $having     = array();
    protected $distinct   = FALSE;
    protected $limit      = FALSE;
    protected $offset     = FALSE;
    protected $lastQuery = '';

    // Stack of queries for push/pop
    protected $queryHistory = array();

    // Column alias
    protected $columnAlias;

    /**
     * Returns a singleton instance of AetherDatabase
     *
     * @param mixed configuration array or DSN
     * @return AetherDatabase
     */
    public static function instance($name = 'default', $config = null) {
        if (!isset(AetherDatabase::$instances[$name])) {
            // Create new instance
            AetherDatabase::$instances[$name] =
                new AetherDatabase($config === null ? $name : $config);
        }

        return AetherDatabase::$instances[$name];
    }

    /**
     * Returns the name of a given database instance.
     *
     * @param Database instance of Database
     * @return string
     */
    public static function instanceName(AetherDatabase $db) {
        return array_search($db, AetherDatabase::$instances, true);
    }

    /**
     * Sets up the database configuration, loads the database driver
     *
     * @param array $config
     */
    public function __construct($config = array()) {
        if (empty($config)) {
            // Load the default config
            $config = AetherDatabaseConfig::retrieve('database.default');
        }
        elseif (is_array($config) && count($config) > 0) {
            if (!array_key_exists('connection', $config)) {
                $config = array('connection' => $config);
            }
        }
        elseif (is_string($config)) {
            // The config is a DSN string
            if (strpos($config, '://') !== false) {
                $config = array('connection' => $config);
            }
            else {
                $name = $config;
                // Test the config name

                if (($config =
                     AetherDatabaseConfig::retrieve('database.' . $config)) === null)
                    throw new DatabaseException("undefined config $name");
            }
        }

        // Merge the default config with the passed config
        $this->config = array_merge($this->config, $config);

        // If the connection is a DSN string
        If (is_string($this->config['connection'])) {
            // Make sure the connection is valid
            if (strpos($this->config['connection'], '://') === false)
                throw new DatabaseException('invalid_dsn ' . $this->config['connection']);

            // Parse the DSN, creating an array to hold the connection parameters
            $db = array(
                'type'     => FALSE,
                'user'     => FALSE,
                'pass'     => FALSE,
                'host'     => FALSE,
                'port'     => FALSE,
                'socket'   => FALSE,
                'database' => FALSE
                );

            // Get the protocol and arguments
            list($db['type'], $connection) =
                explode('://', $this->config['connection'], 2);
            
            if (strpos($connection, '@') !== false) {
                // Get the username and password
                list ($db['pass'], $connection) = explode('@', $connection, 2);
                
                // Check if a password is supplied
                $logindata = explode(':', $db['pass'], 2);
                $db['pass'] = (count($logindata) > 1) ? $logindata[1] : '';
                $db['user'] = $logindata[0];

                // Prepare for finding the database
                $connection = explode('/', $connection);

                // Find the database name
                $db['database'] = array_pop($connection);

                // Reset connection string
                $connection = implode('/', $connection);

                // Find the socket
                if (preg_match('/^unix\([^)]++\)/', $connection)) {
                    // This one is a little hairy: we explode based on the end of
                    // the socket, removing the 'unix(' from the connection string
                    list($db['socket'], $connection) =
                        explode(')', substr($connection, 5), 2);
                }
                elseif (strpos($connection, ':') !== false) {
                    // Fetch the host and port name
                    list ($db['host'], $db['port']) = explode(':', $connection, 2);
                }
                else {
                    $db['host'] = $connection;
                }
            }
            else {
                // File connection
                $connection = explode('/', $connection);

                // Find database file name
                $db['database'] = array_pop($connection);

                // Find database directory name
                $db['socket'] = implode('/', $connection).'/';
            }

            // Reset the connection array to the database config
            $this->config['connection'] = $db;
        }

        // Set the driver name
        $driver = 'AetherDatabase' . ucfirst($this->config['connection']['type']) .
            'Driver';

        // Load the driver
        if (!AetherDatabaseConfig::autoLoad($driver))
            throw new DatabaseException('driver_not_found ' .
                                        $this->config['connection']['type']);

        // Initialize the driver
        $this->driver = new $driver($this->config);

        // Validate the driver
        if (!($this->driver instanceof AetherDatabaseDriver))
            throw new DatabaseException('driver ' .
                                        $this->config['connection']['type'] .
                                        ' does not implement DatabaseDriver');
        
        // ::log('debug', 'Database library initialized');
    }

    /**
     * Simple connect method to get the database queries up and running.
     *
     * @return void
     */
    public function connect() {
        // A link can be a resource or an object
        if (!is_resource($this->link) && !is_object($this->link)) {
            $this->link = $this->driver->connect();
            if (!is_resource($this->link) && !is_object($this->link))
                throw new DatabaseException('connection error: ' .
                                            $this->driver->showError());

            // Clear password after successful connect
            $this->config['connection']['pass'] = NULL;
        }
    }

    /**
     * Runs a query into the driver and returns the result.
     *
     * @param string $sql SQL query to execute
     * @return AetherDatabaseResult
     */
    public function query($sql = '') {
        if ($sql == '')
            return false;

        // No link? Connect!
        if (!$this->link)
            $this->connect();

        // Start the benchmark
        $start = microtime(TRUE);
        
        // if we have more than one argument ($sql)
        if (func_num_args() > 1) {
            $argv = func_get_args();

            // We dont want the first arguement to the function to
            // be part of the binds
            $binds = (is_array(next($argv))) ?
                current($argv) : array_slice($argv, 1);
        }

        // Compile binds if needed
        if (isset($binds))
            $sql = $this->compileBinds($sql, $binds);

        // Fetch the result
        $result = $this->driver->query($this->lastQuery = $sql);

        // Stop the benchmark
        $stop = microtime(TRUE);

        if ($this->config['benchmark'] == true) {
            // Benchmark the query
            AetherDatabase::$benchmarks[] = array('query' => $sql,
                                                  'time' => $stop - $start,
                                                  'rows' => count($result));
        }

        return $result;
    }

    /**
     * Selects the column names for a database query.
     *
     * @param string $sql string or array of column names to select
     * @return AetherDatabase This AetherDatabase object.
     */
    public function select($sql = '*') {
        if (func_num_args() > 1) {
            $sql = func_get_args();
        }
        elseif (is_string($sql)) {
            $sql = explode(',', $sql);
        }
        else {
            $sql = (array)$sql;
        }

        foreach ($sql as $val) {
            if (($val = trim($val)) === '')
                continue;

            if (strpos($val, '(') === false && $val !== '*') {
                if (preg_match('/^DISTINCT\s++(.+)$/i', $val, $matches)) {
                    // Only prepend with table prefix if table name is specified
                    $val = (strpos($matches[1], '.') !== false) ?
                        $this->config['table_prefix'] . $matches[1] : $matches[1];

                    $this->distinct = true;
                }
                else {
                    $val = (strpos($val, '.') !== false) ?
                        $this->config['table_prefix'] . $val : $val;
                }

                $val = $this->driver->escapeColumn($val);
            }

            $this->select[] = $val;
        }

        return $this;
    }
    
    /**
     * Selects the from table(s) for a database query.
     *
     * @param string $sql string or array of tables to select
     * @return AetherDatabase This AetherDatabase object.
     */
    public function from($sql) {
        if (func_num_args() > 1) {
            $sql = func_get_args();
        }
        elseif (is_string($sql)) {
            $sql = explode(',', $sql);
        }
        else {
            $sql = (array)$sql;
        }

        foreach ($sql as $val) {
            if (is_string($val)) {
                if (($val = trim($val)) === '')
                    continue;

                // TODO: Temporary solution, this should be moved to
                // database driver (AS is checked for twice)
                if (stripos($val, ' AS ') !== false) {
                    $val = str_ireplace(' AS ', ' AS ', $val);

                    list($table, $alias) = explode(' AS ', $val);

                    // Attach prefix to both sides of the AS
                    $val = $this->config['table_prefix'] . $table .
                        ' AS ' . $this->config['table_prefix'] . $alias;
                }
                else {
                    $val = $this->config['table_prefix'] . $val;
                }
            }

            $this->from[] = $val;
        }

        return $this;
    }

    /**
     * Generates the JOIN portion of the query.
     *
     * @param string $table table name
     * @param string|array $key where key or array of key => value pairs
     * @param string $value where value
     * @param string $type type of join
     * @return AetherDatabase This AetherDatabase object.
     */
    public function join($table, $key, $value = NULL, $type = '') {
        $join = array();

        if (!empty($type)) {
            $type = strtoupper(trim($type));

            if (!in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER',
                                       'LEFT OUTER', 'RIGHT OUTER'), true)) {
                $type = '';
            }
            else {
                $type .= ' ';
            }
        }

        $cond = array();
        $keys  = is_array($key) ? $key : array($key => $value);
        foreach ($keys as $key => $value) {
            $key = (strpos($key, '.') !== false) ?
                $this->config['table_prefix'].$key : $key;

            if (is_string($value)) {
                // Only escape if it's a string
                $value =
                    $this->driver->escapeColumn($this->config['table_prefix'] .
                                                $value);
            }

            $cond[] = $this->driver->where($key, $value, 'AND ',
                                           count($cond), false);
        }

        if (!is_array($this->join))
            $this->join = array();

        if (!is_array($table))
            $table = array($table);

        foreach ($table as $t) {
            if (is_string($t)) {
                // TODO: Temporary solution, this should be moved to
                // database driver (AS is checked for twice)
                if (stripos($t, ' AS ') !== false) {
                    $t = str_ireplace(' AS ', ' AS ', $t);

                    list($table, $alias) = explode(' AS ', $t);

                    // Attach prefix to both sides of the AS
                    $t = $this->config['table_prefix'] . $table .
                        ' AS ' . $this->config['table_prefix'] . $alias;
                }
                else {
                    $t = $this->config['table_prefix'] . $t;
                }
            }

            $join['tables'][] = $this->driver->escapeColumn($t);
        }

        $join['conditions'] = '('.trim(implode(' ', $cond)).')';
        $join['type'] = $type;

        $this->join[] = $join;

        return $this;
    }


    /**
     * Selects the where(s) for a database query.
     *
     * @param string|array $key key name or array of key => value pairs
     * @param string $value value to match with key
     * @param boolean $quote disable quoting of WHERE clause
     * @return AetherDatabase This AetherDatabase object.
     */
    public function where($key, $value = NULL, $quote = true) {
        $quote = (func_num_args() < 2 && !is_array($key)) ? -1 : $quote;
        
        if (is_object($key)) {
            $keys = array((string) $key => '');
        }
        elseif (!is_array($key)) {
            $keys = array($key => $value);
        }
        else {
            $keys = $key;
        }

        foreach ($keys as $key => $value) {
            $key = (strpos($key, '.') !== false) ?
                $this->config['table_prefix'] . $key : $key;
            
            $this->where[] = $this->driver->where($key, $value, 'AND ',
                                                  count($this->where), $quote);
        }

        return $this;
    }

    /**
     * Selects the or where(s) for a database query.
     *
     * @param string|array $key key name or array of key => value pairs
     * @param string $value value to match with key
     * @param boolean $quote disable quoting of WHERE clause
     * @return AetherDatabase This AetherDatabase object.
     */
    public function orwhere($key, $value = NULL, $quote = true) {
        $quote = (func_num_args() < 2 && !is_array($key)) ? -1 : $quote;
        
        if (is_object($key)) {
            $keys = array((string)$key => '');
        }
        elseif (!is_array($key)) {
            $keys = array($key => $value);
        }
        else {
            $keys = $key;
        }

        foreach ($keys as $key => $value) {
            $key = (strpos($key, '.') !== false) ?
                $this->config['table_prefix'] . $key : $key;
            
            $this->where[] = $this->driver->where($key, $value, 'OR ',
                                                  count($this->where), $quote);
        }

        return $this;
    }

    /**
     * Selects the like(s) for a database query.
     *
     * @param string|array $field field name or array of field => match pairs
     * @param string $match like value to match with field
     * @param boolean $auto automatically add starting and ending wildcards
     * @return AetherDatabase This AetherDatabase object.
     */
    public function like($field, $match = '', $auto = true) {
        $fields = is_array($field) ? $field : array($field => $match);

        foreach ($fields as $field => $match) {
            $field = (strpos($field, '.') !== false) ?
                $this->config['table_prefix'] . $field : $field;
            
            $this->where[] = $this->driver->like($field, $match, $auto,
                                                 'AND ', count($this->where));
        }

        return $this;
    }

    /**
     * Selects the or like(s) for a database query.
     *
     * @param string|array $field field name or array of field => match pairs
     * @param string $match like value to match with field
     * @param boolean $auto automatically add starting and ending wildcards
     * @return AetherDatabase This AetherDatabase object.
     */
    public function orlike($field, $match = '', $auto = true) {
        $fields = is_array($field) ? $field : array($field => $match);

        foreach ($fields as $field => $match) {
            $field = (strpos($field, '.') !== false) ?
                $this->config['table_prefix'] . $field : $field;
            
            $this->where[] = $this->driver->like($field, $match, $auto,
                                                 'OR ', count($this->where));
        }

        return $this;
    }

    /**
     * Selects the not like(s) for a database query.
     *
     * @param string|array $field field name or array of field => match pairs
     * @param string $match like value to match with field
     * @param boolean $auto automatically add starting and ending wildcards
     * @return AetherDatabase This AehterDatabase object.
     */
    public function notlike($field, $match = '', $auto = true) {
        $fields = is_array($field) ? $field : array($field => $match);

        foreach ($fields as $field => $match) {
            $field = (strpos($field, '.') !== false) ?
                $this->config['table_prefix'] . $field : $field;
            
            $this->where[] = $this->driver->notlike($field, $match, $auto,
                                                    'AND ', count($this->where));
        }

        return $this;
    }

    /**
     * Selects the or not like(s) for a database query.
     *
     * @param string|array $field field name or array of field => match pairs
     * @param string $match like value to match with field
     * @return AetherDatabase This AetherDatabase object.
     */
    public function ornotlike($field, $match = '', $auto = true) {
        $fields = is_array($field) ? $field : array($field => $match);

        foreach ($fields as $field => $match) {
            $field = (strpos($field, '.') !== false) ?
                $this->config['table_prefix'] . $field : $field;
            
            $this->where[] = $this->driver->notlike($field, $match, $auto,
                                                    'OR ', count($this->where));
        }

        return $this;
    }

    /**
     * Selects the like(s) for a database query.
     *
     * @param string|array $field field name or array of field => match pairs
     * @param string $match like value to match with field
     * @return AetherDatabase This AetherDatabase object.
     */
    public function regex($field, $match = '') {
        $fields = is_array($field) ? $field : array($field => $match);

        foreach ($fields as $field => $match) {
            $field = (strpos($field, '.') !== false) ?
                $this->config['table_prefix'] . $field : $field;
            
            $this->where[] = $this->driver->regex($field, $match, 'AND ',
                                                  count($this->where));
        }

        return $this;
    }

    /**
     * Selects the or like(s) for a database query.
     *
     * @param string|array $field field name or array of field => match pairs
     * @param string $match like value to match with field
     * @return AetherDatabase This AetherDatabase object.
     */
    public function orregex($field, $match = '') {
        $fields = is_array($field) ? $field : array($field => $match);

        foreach ($fields as $field => $match) {
            $field = (strpos($field, '.') !== false) ?
                $this->config['table_prefix'] . $field : $field;
            
            $this->where[] = $this->driver->regex($field, $match, 'OR ',
                                                  count($this->where));
        }

        return $this;
    }

    /**
     * Selects the not regex(s) for a database query.
     *
     * @param string|array $field field name or array of field => match pairs
     * @param string $match regex value to match with field
     * @return AetherDatabase This AetherDatabase object.
     */
    public function notregex($field, $match = '') {
        $fields = is_array($field) ? $field : array($field => $match);

        foreach ($fields as $field => $match) {
            $field = (strpos($field, '.') !== false) ?
                $this->config['table_prefix'] . $field : $field;
            
            $this->where[] = $this->driver->notregex($field, $match, 'AND ',
                                                     count($this->where));
        }

        return $this;
    }

    /**
     * Selects the or not regex(s) for a database query.
     *
     * @param string|array $field field name or array of field => match pairs
     * @param string $match regex value to match with field
     * @return AetherDatabase This AetherDatabase object.
     */
    public function ornotregex($field, $match = '') {
        $fields = is_array($field) ? $field : array($field => $match);

        foreach ($fields as $field => $match) {
            $field = (strpos($field, '.') !== FALSE) ?
                $this->config['table_prefix'] . $field : $field;
            
            $this->where[] = $this->driver->notregex($field, $match, 'OR ',
                                                     count($this->where));
        }

        return $this;
    }

    /**
     * Chooses the column to group by in a select query.
     *
     * @param string $by column name to group by
     * @return AetherDatabase  This AetherDatabase object.
     */
    public function groupby($by) {
        if (!is_array($by)) {
            $by = explode(',', (string) $by);
        }

        foreach ($by as $val) {
            $val = trim($val);

            if ($val != '') {
                // Add the table prefix if we are using table.column names
                if(strpos($val, '.'))
                    $val = $this->config['table_prefix'] . $val;

                $this->groupby[] = $this->driver->escapeColumn($val);
            }
        }

        return $this;
    }

    /**
     * Selects the having(s) for a database query.
     *
     * @param string|array $key key name or array of key => value pairs
     * @param string $value value to match with key
     * @param boolean $quote disable quoting of WHERE clause
     * @return AehterDatabase This AetherDatabase object.
     */
    public function having($key, $value = '', $quote = true) {
        $this->having[] = $this->driver->where($key, $value, 'AND',
                                               count($this->having), true);
        return $this;
    }

    /**
     * Selects the or having(s) for a database query.
     *
     * @param string|array $key key name or array of key => value pairs
     * @param string $value value to match with key
     * @param boolean $quote disable quoting of WHERE clause
     * @return AetherDatabase This AehterDatabase object.
     */
    public function orhaving($key, $value = '', $quote = true) {
        $this->having[] = $this->driver->where($key, $value, 'OR', count($this->having), TRUE);
        return $this;
    }

    /**
     * Chooses which column(s) to order the select query by.
     *
     * @param string|array $orderby column(s) to order on, can be an array,
     * single column, or comma seperated list of columns
     * @param string $direction direction of the order
     * @return AetherDatabase This AetherDatabase object.
     */
    public function orderby($orderby, $direction = NULL) {
        if (!is_array($orderby))
            $orderby = array($orderby => $direction);

        foreach ($orderby as $column => $direction) {
            $direction = strtoupper(trim($direction));

            // Add a direction if the provided one isn't valid
            if (!in_array($direction, array('ASC', 'DESC', 'RAND()',
                                            'RANDOM()', 'NULL'))) {
                $direction = 'ASC';
            }

            // Add the table prefix if a table.column was passed
            if (strpos($column, '.'))
                $column = $this->config['table_prefix'] . $column;

            $this->orderby[] = $this->driver->escapeColumn($column) .
                ' ' . $direction;
        }

        return $this;
    }

    /**
     * Selects the limit section of a query.
     *
     * @param integer $limit number of rows to limit result to
     * @param integer $offset offset in result to start returning rows from
     * @return AetherDatabase This AetherDatabase object.
     */
    public function limit($limit, $offset = NULL) {
        $this->limit = (int)$limit;

        if ($offset !== NULL || !is_int($this->offset))
            $this->offset($offset);

        return $this;
    }

    /**
     * Sets the offset portion of a query.
     *
     * @param integer $value offset value
     * @return AetherDatabase This AetherDatabase object.
     */
    public function offset($value) {
        $this->offset = (int)$value;

        return $this;
    }

    /**
     * Allows key/value pairs to be set for inserting or updating.
     *
     * @param string|array $key key name or array of key => value pairs
     * @param string $value value to match with key
     * @return AetherDatabase This AetherDatabase object.
     */
    public function set($key, $value = '') {
        if (!is_array($key))
            $key = array($key => $value);

        foreach ($key as $k => $v) {
            // Add a table prefix if the column includes the table.
            if (strpos($k, '.'))
                $k = $this->config['table_prefix'] . $k;

            $this->set[$k] = $this->driver->escape($v);
        }

        return $this;
    }

    /**
     * Compiles the select statement based on the other functions called and runs the query.
     *
     * @param string $table table name
     * @param string $limit limit clause
     * @param string $offset offset clause
     * @return AetherDatabase
     */
    public function get($table = '', $limit = NULL, $offset = NULL) {
        if ($table != '')
            $this->from($table);

        if (!is_null($limit))
            $this->limit($limit, $offset);

        $sql = $this->driver->compileSelect(get_object_vars($this));

        $this->resetSelect();

        $result = $this->query($sql);

        $this->lastQuery = $sql;

        return $result;
    }

    /**
     * Compiles the select statement based on the other functions called and runs the query.
     *
     * @param string $table table name
     * @param array $where  where clause
     * @param string $limit limit clause
     * @param string $offset offset clause
     * @return AetherDatabase This AetherDatabase object.
     */
    public function getwhere($table = '', $where = NULL, $limit = NULL, $offset = NULL) {
        if ($table != '')
            $this->from($table);

        if (!is_null($where))
            $this->where($where);

        if (!is_null($limit))
            $this->limit($limit, $offset);

        $sql = $this->driver->compileSelect(get_object_vars($this));

        $this->resetSelect();

        $result = $this->query($sql);

        return $result;
    }

    /**
     * Compiles the select statement based on the other
     * functions called and returns the query string.
     *
     * @param string $table table name
     * @param string $limit limit clause
     * @param string $offset offset clause
     * @return string sql string
     */
    public function compile($table = '', $limit = NULL, $offset = NULL) {
        if ($table != '')
            $this->from($table);

        if (!is_null($limit))
            $this->limit($limit, $offset);

        $sql = $this->driver->compileSelect(get_object_vars($this));

        $this->resetSelect();

        return $sql;
    }

    /**
     * Compiles an insert string and runs the query.
     *
     * @param string $table table name
     * @param array $set array of key/value pairs to insert
     * @return AetherDatabase Query result
     */
    public function insert($table = '', $set = NULL) {
        if (!is_null($set))
            $this->set($set);

        if ($this->set == NULL)
            throw new DatabaseException('must_use_set');

        if ($table == '') {
            if (!isset($this->from[0]))
                throw new DatabaseException('database.must_use_table');

            $table = $this->from[0];
        }

        // If caching is enabled, clear the cache before inserting
        if ($this->config['cache'] === true)
            $this->clearCache();

        $sql = $this->driver->insert($this->config['table_prefix'] . $table,
                                     array_keys($this->set),
                                     array_values($this->set));

        $this->resetWrite();

        return $this->query($sql);
    }

    /**
     * Adds an "IN" condition to the where clause
     *
     * @param string $field Name of the column being examined
     * @param mixed $values An array or string to match against
     * @param bool $not Generate a NOT IN clause instead
     * @return AetherDatabase This AetherDatabase object.
     */
    public function in($field, $values, $not = false) {
        if (is_array($values)) {
            $escapedValues = array();
            foreach ($values as $v) {
                if (is_numeric($v))
                    $escapedValues[] = $v;
                else
                    $escapedValues[] = "'" . $this->driver->escape_str($v) . "'";
            }
            
            $values = implode(",", $escapedValues);
        }

        if (strpos($field, '.') !== false)
            $field = $this->config['table_prefix'] . $field;

        $where = $this->driver->escapeColumn($field) . ' ' .
            ($not === true ? 'NOT ' : '') . 'IN (' . $values . ')';
        $this->where[] = $this->driver->where($where, '', 'AND ',
                                              count($this->where), -1);

        return $this;
    }

    /**
     * Adds a "NOT IN" condition to the where clause
     *
     * @param string $field Name of the column being examined
     * @param mixed $values An array or string to match against
     * @return AetherDatabase  This AetherDatabase object.
     */
    public function notin($field, $values) {
        return $this->in($field, $values, true);
    }

    /**
     * Compiles a merge string and runs the query.
     *
     * @param string $table table name
     * @param array $set array of key/value pairs to merge
     * @return AetherDatabase Query result
     */
    public function merge($table = '', $set = NULL) {
        if (!is_null($set))
            $this->set($set);

        if ($this->set == NULL)
            throw new DatabaseException('must_use_set');

        if ($table == '') {
            if (!isset($this->from[0]))
                throw new DatabaseException('must_use_table');

            $table = $this->from[0];
        }

        $sql = $this->driver->merge($this->config['table_prefix'] . $table,
                                    array_keys($this->set),
                                    array_values($this->set));

        $this->resetWrite();
        return $this->query($sql);
    }

    /**
     * Compiles an update string and runs the query.
     *
     * @param string $table table name
     * @param array $set associative array of update values
     * @param array $where where clause
     * @return AetherDatabase  Query result
     */
    public function update($table = '', $set = NULL, $where = NULL) {
        if (is_array($set))
            $this->set($set);

        if (!is_null($where))
            $this->where($where);

        if ($this->set == false)
            throw new DatabaseException('must_use_set');

        if ($table == '') {
            if (!isset($this->from[0]))
                throw new DatabaseException('must_use_table');

            $table = $this->from[0];
        }

        $sql = $this->driver->update($this->config['table_prefix'] . $table,
                                     $this->set, $this->where);

        $this->resetWrite();
        return $this->query($sql);
    }

    /**
     * Compiles a delete string and runs the query.
     *
     * @param string $table table name
     * @param array $where where clause
     * @return AetherDatabase Query result
     */
    public function delete($table = '', $where = NULL) {
        if ($table == '') {
            if (!isset($this->from[0]))
                throw new DatabaseException('must_use_table');

            $table = $this->from[0];
        }
        else {
            $table = $this->config['table_prefix'] . $table;
        }

        if (!is_null($where))
            $this->where($where);

        if (count($this->where) < 1)
            throw new DatabaseException('must_use_where');

        $sql = $this->driver->delete($table, $this->where);

        $this->resetWrite();
        return $this->query($sql);
    }

    /**
     * Returns the last query run.
     *
     * @return string SQL
     */
    public function lastQuery() {
        return $this->lastQuery;
    }

    /**
     * Count query records.
     *
     * @param string $table table name
     * @param array $where where clause
     * @return integer
     */
    public function countRecords($table = false, $where = NULL) {
        if (count($this->from) < 1) {
            if ($table == FALSE)
                throw new DatabaseException('must_use_table');

            $this->from($table);
        }

        if ($where !== NULL)
            $this->where($where);

        $query = $this->select('COUNT(*) AS ' . $this->escapeColumn('records_found'))->
            get()->result(true);

        return (int)$query->current()->records_found;
    }

    /**
     * Resets all private select variables.
     *
     * @return void
     */
    protected function resetSelect() {
        $this->select   = array();
        $this->from     = array();
        $this->join     = array();
        $this->where    = array();
        $this->orderby  = array();
        $this->groupby  = array();
        $this->having   = array();
        $this->distinct = FALSE;
        $this->limit    = FALSE;
        $this->offset   = FALSE;
    }

    /**
     * Resets all private insert and update variables.
     *
     * @return  void
     */
    protected function resetWrite() {
        $this->set   = array();
        $this->from  = array();
        $this->where = array();
    }

    /**
     * Lists all the tables in the current database.
     *
     * @return  array
     */
    public function listTables() {
        if (!$this->link)
            $this->connect();

        return $this->driver->listTables();
    }

    /**
     * See if a table exists in the database.
     *
     * @param string $tableName table name
     * @param boolean $prefix True to attach table prefix
     * @return boolean
     */
    public function tableExists($table_name, $prefix = true) {
        if ($prefix)
            return in_array($this->config['table_prefix'] . $table_name,
                            $this->listTables());
        else
            return in_array($table_name, $this->listTables());
    }

    /**
     * Combine a SQL statement with the bind values. Used for safe queries.
     *
     * @param string $sql query to bind to the values
     * @param array $binds array of values to bind to the query
     * @return string
     */
    public function compileBinds($sql, $binds) {
        foreach ((array)$binds as $val) {
            // If the SQL contains no more bind marks ("?"), we're done.
            if (($nextBindPos = strpos($sql, '?')) === false)
                break;

            // Properly escape the bind value.
            $val = $this->driver->escape($val);

            // Temporarily replace possible bind marks ("?"), in the bind value itself, with a placeholder.
            $val = str_replace('?', '{%B%}', $val);

            // Replace the first bind mark ("?") with its corresponding value.
            $sql = substr($sql, 0, $nextBindPos) . $val .
                substr($sql, $next_bind_pos + 1);
        }

        // Restore placeholders.
        return str_replace('{%B%}', '?', $sql);
    }

    /**
     * Get the field data for a database table, along with the field's attributes.
     *
     * @param string $table table name
     * @return array
     */
    public function fieldData($table = '') {
        if (!$this->link)
            $this->connect();

        return $this->driver->fieldData($this->config['table_prefix'] . $table);
    }

    /**
     * Get the field data for a database table, along with the field's attributes.
     *
     * @param string $table table name
     * @return array
     */
    public function listFields($table = '') {
        if (!$this->link)
            $this->connect();

        return $this->driver->listFields($this->config['table_prefix'] . $table);
    }

    /**
     * Escapes a value for a query.
     *
     * @param mixed $value value to escape
     * @return string
     */
    public function escape($value) {
        return $this->driver->escape($value);
    }

    /**
     * Escapes a string for a query.
     *
     * @param string $str string to escape
     * @return string
     */
    public function escapeStr($str) {
        return $this->driver->escapeStr($str);
    }

    /**
     * Escapes a table name for a query.
     *
     * @param string $table string to escape
     * @return string
     */
    public function escapeTable($table) {
        return $this->driver->escapeTable($table);
    }

    /**
     * Escapes a column name for a query.
     *
     * @param string $table string to escape
     * @return string
     */
    public function escapeColumn($table) {
        return $this->driver->escapeColumn($table);
    }

    /**
     * Returns table prefix of current configuration.
     *
     * @return  string
     */
    public function tablePrefix() {
        return $this->config['table_prefix'];
    }

    /**
     * Clears the query cache.
     *
     * @param string|TRUE $sql clear cache by SQL statement or TRUE for last query
     * @return AetherDatabase This AetherDatabase object.
     */
    public function clearCache($sql = NULL)
    {
        if ($sql === true) {
            $this->driver->clearCache($this->lastQuery);
        }
        elseif (is_string($sql)) {
            $this->driver->clearCache($sql);
        }
        else {
            $this->driver->clearCache();
        }

        return $this;
    }

    /**
     * Pushes existing query space onto the query stack.  Use push
     * and pop to prevent queries from clashing before they are
     * executed
     *
     * @return AetherDatabase This Databaes object
     */
    public function push() {
        array_push($this->query_history, array(
                       $this->select,
                       $this->from,
                       $this->join,
                       $this->where,
                       $this->orderby,
                       $this->order,
                       $this->groupby,
                       $this->having,
                       $this->distinct,
                       $this->limit,
                       $this->offset
                       )
            );

        $this->resetSelect();

        return $this;
    }

    /**
     * Pops from query stack into the current query space.
     *
     * @return AetherDatabase This Databaes object
     */
    public function pop() {
        if (count($this->queryHistory) == 0) {
            // No history
            return $this;
        }

        list(
            $this->select,
            $this->from,
            $this->join,
            $this->where,
            $this->orderby,
            $this->order,
            $this->groupby,
            $this->having,
            $this->distinct,
            $this->limit,
            $this->offset
            ) = array_pop($this->queryHistory);

        return $this;
    }

    /**
     * Count the number of records in the last query, without LIMIT or OFFSET applied.
     *
     * @return  integer
     */
    public function countLastQuery() {
        if ($sql = $this->lastQuery()) {
            if (stripos($sql, 'LIMIT') !== false) {
                // Remove LIMIT from the SQL
                $sql = preg_replace('/\sLIMIT\s+[^a-z]+/i', ' ', $sql);
            }

            if (stripos($sql, 'OFFSET') !== false) {
                // Remove OFFSET from the SQL
                $sql = preg_replace('/\sOFFSET\s+\d+/i', '', $sql);
            }

            // Get the total rows from the last query executed
            $result = $this->query(
                'SELECT COUNT(*) AS ' . $this->escapeColumn('total_rows') .
                ' FROM (' . trim($sql) . ') AS ' .
                $this->escapeTable('counted_results')
                );

            // Return the total number of rows from the query
            return (int)$result->current()->total_rows;
        }

        return false;
    }

    /**
     * Sets aliases for certain columns in a table
     *
     * @param string $table Table to set alias for
     * @param array $aliases array with aliases
     * @return void
     */
    public function setColumnAlias($table, $aliases) {
        if ($this->columnAlias === NULL)
            $this->columnAlias = array();

        if (!is_array($aliases) && $aliases !== NULL)
            throw new Exception('Aliases needs to be an array or NULL');

        $this->columnAlias[$table] = $aliases;
    }

    /**
     * Get the alias for a column name
     * 
     * @param string $table Table to get alias for
     * @param string $column Column to get alias for
     * @return mixed
     */
    public function getColumnAlias($table, $column) {
        if (!isset($this->columnAlias[$table]) ||
            !isset($this->columnAlias[$table][$column]))
            return NULL;

        return $this->columnAlias[$table][$column];
    }

    /**
     * Get a column name by resolving an alias
     *
     * @param string $table Table to get column name for
     * @param string $column Alias to get column name for
     * @return mixed
     */
    public function getColumnByAlias($table, $alias) {
        if (!isset($this->columnAlias[$table]))
            return NULL;
        
        $array = array_flip($this->columnAlias[$table]);

        if (isset($array[$alias]))
            return $array[$alias];
        else
            return NULL;
    }
}

class DatabaseException extends Exception {
}