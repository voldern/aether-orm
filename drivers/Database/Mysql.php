<?php

class DatabaseMysqlDriver extends DatabaseDriver {

	/**
	 * Database connection link
	 */
	protected $link;

	/**
	 * Database configuration
	 */
	protected $dbConfig;

	/**
	 * Sets the config for the class.
	 *
	 * @param array $config database configuration
	 */
	public function __construct($config) {
		$this->dbConfig = $config;

		// ::log('debug', 'MySQL Database Driver Initialized');
	}

	/**
	 * Closes the database connection.
	 */
	public function __destruct() {
        if (is_resource($this->link))
            mysql_close($this->link);
	}

	public function connect() {
		// Check if link already exists
		if (is_resource($this->link))
			return $this->link;

		// Import the connect variables
		extract($this->dbConfig['connection']);

		// Persistent connections enabled?
		$connect = ($this->dbConfig['persistent'] == true) ?
            'mysql_pconnect' : 'mysql_connect';

		// Build the connection info
		$host = isset($host) ? $host : $socket;
		$port = isset($port) ? ':'.$port : '';

		// Make the connection and select the database
		if (($this->link = $connect($host.$port, $user, $pass, true)) &&
            mysql_select_db($database, $this->link)) {
            
			if ($charset = $this->dbConfig['character_set'])
				$this->setCharset($charset);

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
                    new MysqlResult(mysql_query($sql, $this->link),
                                    $this->link, $this->dbConfig['object'], $sql);
			}
			else {
				// Rewind cached result
				$this->queryCache[$hash]->rewind();
			}

			// Return the cached query
			return $this->queryCache[$hash];
		}

		return new MysqlResult(mysql_query($sql, $this->link), $this->link,
                               $this->dbConfig['object'], $sql);
	}

	public function setCharset($charset) {
		$this->query('SET NAMES '.$this->escapeStr($charset));
	}

	public function escapeTable($table) {
		if (!$this->dbConfig['escape'])
			return $table;

		if (stripos($table, ' AS ') !== false) {
			// Force 'AS' to uppercase
			$table = str_ireplace(' AS ', ' AS ', $table);

			// Runs escape_table on both sides of an AS statement
			$table = array_map(array($this, __FUNCTION__),
                               explode(' AS ', $table));

			// Re-create the AS statement
			return implode(' AS ', $table);
		}
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
				return $matches[1] . '(' . $this->escapeColumn($matches[2]) . ')';
			elseif (count($matches) == 5)
				return $matches[1] . '(' . $this->escapeColumn($matches[2]) .
                ') AS ' . $this->escapeColumn($matches[2]);
		}
		
		// This matches any modifiers we support to SELECT.
        $regex = '/\b(?:rand|all|distinct(?:row)?|high_priority|sql_ ' .
            '(?:small_result|b(?:ig_result|uffer_result)|no_cache|' .
            'ca(?:che|lc_found_rows)))\s/i';
        if (!preg_match($regex, $column)) {
			if (stripos($column, ' AS ') !== false) {
				// Force 'AS' to uppercase
				$column = str_ireplace(' AS ', ' AS ', $column);

				// Runs escape_column on both sides of an AS statement
				$column = array_map(array($this, __FUNCTION__),
                                    explode(' AS ', $column));

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

	public function regex($field, $match, $type, $numRegexs) {
		$prefix = ($numRegexs == 0) ? '' : $type;

		return $prefix.' '.$this->escapeColumn($field) .
            ' REGEXP \'' . $this->escapeStr($match).'\'';
	}

	public function notregex($field, $match, $type, $numRegexs) {
		$prefix = $numRegexs == 0 ? '' : $type;

		return $prefix . ' ' . $this->escapeColumn($field) .
            ' NOT REGEXP \'' . $this->escapeStr($match) . '\'';
	}

	public function merge($table, $keys, $values) {
		// Escape the column names
		foreach ($keys as $key => $value)
			$keys[$key] = $this->escapeColumn($value);
        
		return 'REPLACE INTO ' . $this->escapeTable($table) . ' (' .
            implode(', ', $keys) . ') VALUES (' . implode(', ', $values).')';
	}

	public function limit($limit, $offset = 0) {
		return 'LIMIT '.$offset.', '.$limit;
	}

	public function compileSelect($database) {
		$sql = ($database['distinct'] == true) ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= (count($database['select']) > 0) ?
            implode(', ', $database['select']) : '*';

		if (count($database['from']) > 0) {
			// Escape the tables
			$froms = array();
			foreach ($database['from'] as $from)
				$froms[] = $this->escapeColumn($from);

			$sql .= "\nFROM (";
			$sql .= implode(', ', $froms).")";
		}

		if (count($database['join']) > 0) {
			foreach($database['join'] AS $join)
				$sql .= "\n" . $join['type'] . 'JOIN ' .
                implode(', ', $join['tables']) . ' ON ' . $join['conditions'];
		}

		if (count($database['where']) > 0)
			$sql .= "\nWHERE ";

		$sql .= implode("\n", $database['where']);

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

        if (!is_resource($this->link))
            $this->connect();

		return mysql_real_escape_string($str, $this->link);
	}

	public function listTables() {
		$tables = array();

        $query = $this->query('SHOW TABLES FROM ' .
                              $this->escapeTable(
                                  $this->dbConfig['connection']['database']));
		if ($query) {
			foreach ($query->result(false) as $row)
				$tables[] = current($row);
		}

		return $tables;
	}

	public function showError() {
		return mysql_error($this->link);
	}

	public function listFields($table) {
		$result = NULL;

		foreach ($this->fieldData($table) as $row) {
			// Make an associative array
			$result[$row->Field] = $this->sqlType($row->Type);

			if ($row->Key === 'PRI' && $row->Extra === 'auto_increment') {
				// For sequenced (AUTO_INCREMENT) tables
				$result[$row->Field]['sequenced'] = true;
			}

			if ($row->Null === 'YES') {
				// Set NULL status
				$result[$row->Field]['null'] = true;
			}
		}

		if (!isset($result))
			throw new DatabaseException('table_not_found ' . $table);

		return $result;
	}

	public function fieldData($table) {
		$result = $this->query('SHOW COLUMNS FROM ' . $this->escapeTable($table));

		return $result->resultArray(true);
	}

}

/**
 * MySQL Result
 */
class MysqlResult extends DatabaseResult {

	// Fetch function and return type
	protected $fetchType  = 'mysql_fetch_object';
	protected $returnType = MYSQL_ASSOC;

	/**
	 * Sets up the result variables.
	 *
	 * @param resource $result query result
	 * @param resource $link database link
	 * @param boolean $object return objects or arrays
	 * @param string $sql SQL query that was run
	 */
	public function __construct($result, $link, $object = true, $sql) {
		$this->result = $result;

		// If the query is a resource, it was a SELECT, SHOW, DESCRIBE, EXPLAIN query
		if (is_resource($result)) {
			$this->currentRow = 0;
			$this->totalRows  = mysql_num_rows($this->result);
			$this->fetchType = ($object === true) ?
                'mysql_fetch_object' : 'mysql_fetch_array';
		}
		elseif (is_bool($result)) {
			if ($result == false) {
				// SQL error
				throw new DatabaseException('mysql error: ' .  mysql_error($link)
                                            . ' - ' . $sql);
			}
			else {
				// Its an DELETE, INSERT, REPLACE, or UPDATE query
				$this->insertId  = mysql_insert_id($link);
				$this->totalRows = mysql_affected_rows($link);
			}
		}

		// Set result type
		$this->result($object);

		// Store the SQL
		$this->sql = $sql;
	}

	/**
	 * Destruct, the cleanup crew!
	 */
	public function __destruct() {
		if (is_resource($this->result))
			mysql_free_result($this->result);
	}

	public function result($object = true, $type = MYSQL_ASSOC) {
		$this->fetchType = ((bool)$object) ?
            'mysql_fetch_object' : 'mysql_fetch_array';

		// This check has to be outside the previous statement, because we do not
		// know the state of fetch_type when $object = NULL
		// NOTE - The class set by $type must be defined before fetching the result,
		// autoloading is disabled to save a lot of stupid overhead.
		if ($this->fetchType == 'mysql_fetch_object' && $object === true)
			$this->returnType = (is_string($type) && Config::autoLoad($type))
                ? $type : 'stdClass';
		else
			$this->returnType = $type;

		return $this;
	}

	public function asArray($object = NULL, $type = MYSQL_ASSOC) {
		return $this->resultArray($object, $type);
	}

	public function resultArray($object = NULL, $type = MYSQL_ASSOC) {
		$rows = array();

		if (is_string($object)) {
			$fetch = $object;
		}
		elseif (is_bool($object)) {
			if ($object === true) {
				$fetch = 'mysql_fetch_object';

				$type = (is_string($type) && Config::autoLoad($type)) ?
                    $type : 'stdClass';
			}
			else {
				$fetch = 'mysql_fetch_array';
			}
		}
		else {
			// Use the default config values
			$fetch = $this->fetchType;

			if ($fetch == 'mysql_fetch_object') {
                if (is_string($this->returnType) &&
                    Config::autoLoad($this->returnType)) {
                    $type = $this->returnType;
                }
                else
                    $type = 'stdClass';
			}
		}

		if (mysql_num_rows($this->result)) {
			// Reset the pointer location to make sure things work properly
			mysql_data_seek($this->result, 0);

			while ($row = $fetch($this->result, $type)) {
				$rows[] = $row;
			}
		}

		return isset($rows) ? $rows : array();
	}

	public function listFields() {
		$fieldNames = array();
		while ($field = mysql_fetch_field($this->result))
			$fieldNames[] = $field->name;

		return $fieldNames;
	}

	public function seek($offset) {
		if ($this->offsetExists($offset) && mysql_data_seek($this->result, $offset)) {
			// Set the current row to the offset
			$this->currentRow = $offset;

			return true;
		}
		else {
			return false;
		}
	}

}
