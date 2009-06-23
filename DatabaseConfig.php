<?php

$config = array();


$config['default'] = array(
		'benchmark'     => true,
		'persistent'    => false,
		'connection'    => array(
			'type'     => 'mysql',
			'user'     => 'dbuser',
			'pass'     => 'p@ssw0rd',
			'host'     => 'localhost',
			'port'     => false,
			'socket'   => false,
			'database' => 'kohana'
		),
		'character_set' => 'utf8',
		'table_prefix'  => '',
		'object'        => true,
		'cache'         => false,
		'escape'        => true
);

$config['prisguide'] = array(
		'benchmark'     => true,
		'persistent'    => false,
		'connection'    => array(
			'type'     => 'pgsql',
			'user'     => 'prisguide',
			'pass'     => 'j7ieaRKWaNkayD',
			'host'     => 'dev.raw.no',
			'port'     => false,
			'socket'   => false,
			'database' => 'prisguide'
		),
		'character_set' => 'utf8',
		'table_prefix'  => '',
		'object'        => true,
		'cache'         => true,
		'escape'        => true
);

$config['test_mysql'] = array(
		'benchmark'     => true,
		'persistent'    => false,
		'connection'    => array(
			'type'     => 'mysql',
			'user'     => 'testdb',
			'pass'     => 'TYfusaK5CaPaB7fR',
			'host'     => 'dev.raw.no',
			'port'     => false,
			'socket'   => false,
			'database' => 'testdb'
		),
		'character_set' => 'utf8',
		'table_prefix'  => '',
		'object'        => true,
		'cache'         => false,
		'escape'        => true
);

$config['test_mysqli'] = array(
		'benchmark'     => true,
		'persistent'    => false,
		'connection'    => array(
			'type'     => 'mysqli',
			'user'     => 'testdb',
			'pass'     => 'TYfusaK5CaPaB7fR',
			'host'     => 'dev.raw.no',
			'port'     => false,
			'socket'   => false,
			'database' => 'testdb'
		),
		'character_set' => 'utf8',
		'table_prefix'  => '',
		'object'        => true,
		'cache'         => false,
		'escape'        => true
);

$config['sql_types'] = array(
    'tinyint' => array('type' => 'int', 'max' => 127),
    'smallint' => array('type' => 'int', 'max' => 32767),
    'mediumint'	=> array('type' => 'int', 'max' => 8388607),
    'int' => array('type' => 'int', 'max' => 2147483647),
    'integer' => array('type' => 'int', 'max' => 2147483647),
    'bigint' => array('type' => 'int', 'max' => 9223372036854775807),
    'float'	=> array('type' => 'float'),
    'float unsigned' => array('type' => 'float', 'min' => 0),
    'boolean' => array('type' => 'boolean'),
    'time' => array('type' => 'string', 'format' => '00:00:00'),
    'time with time zone' => array('type' => 'string'),
    'date' => array('type' => 'string', 'format' => '0000-00-00'),
    'year' => array('type' => 'string', 'format' => '-1'),
    'datetime' => array('type' => 'string', 'format' => '0000-00-00 00:00:00'),
    'timestamp with time zone' => array('type' => 'string'),
    'char' => array('type' => 'string', 'exact' => TRUE),
    'binary' => array('type' => 'string', 'binary' => TRUE, 'exact' => TRUE),
    'varchar' => array('type' => 'string'),
    'varbinary' => array('type' => 'string', 'binary' => TRUE),
    'blob' => array('type' => 'string', 'binary' => TRUE),
    'text' => array('type' => 'string')
    );

// double
$config['sql_types']['double'] = $config['sql_types']['double precision'] =
    $config['sql_types']['decimal'] = $config['sql_types']['real'] =
    $config['sql_types']['numeric'] = $config['sql_types']['float'];

// bit
$config['sql_types']['bit'] = $config['sql_types']['boolean'];

// timestamp
$config['sql_types']['timestamp'] =
    $config['sql_types']['timestamp without time zone'] =
    $config['sql_types']['datetime'];

// enum
$config['sql_types']['enum'] = $config['sql_types']['set'] =
    $config['sql_types']['varchar'];

// text
$config['tinytext'] = $config['sql_types']['mediumtext'] =
    $config['sql_types']['longtext'] = $config['sql_types']['text'];

// blob
$config['sql_types']['tsvector'] = $config['sql_types']['tinyblob'] =
    $config['sql_types']['mediumblob'] = $config['sql_types']['longblob'] =
    $config['sql_types']['clob'] = $config['sql_types']['bytea'] =
    $config['sql_types']['blob'];

// CHARACTER
$config['sql_types']['character'] = $config['sql_types']['char'];
$config['sql_types']['character varying'] = $config['sql_types']['varchar'];

// TIME
$config['sql_types']['time without time zone'] = $config['sql_types']['time'];
