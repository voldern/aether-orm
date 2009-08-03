<?php

$config = array();

$config = array(
    'tinyint' => array('type' => 'int', 'max' => 127),
    'smallint' => array('type' => 'int', 'max' => 32767),
    'mediumint' => array('type' => 'int', 'max' => 8388607),
    'int' => array('type' => 'int', 'max' => 2147483647),
    'integer' => array('type' => 'int', 'max' => 2147483647),
    'bigint' => array('type' => 'int', 'max' => 9223372036854775807),
    'float' => array('type' => 'float'),
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
$config['double'] = $config['double precision'] =
    $config['decimal'] = $config['real'] =
    $config['numeric'] = $config['float'];

// bit
$config['bit'] = $config['boolean'];

// timestamp
$config['timestamp'] =
    $config['timestamp without time zone'] =
    $config['datetime'];

// enum
$config['enum'] = $config['set'] =
    $config['varchar'];

// text
$config['tinytext'] = $config['mediumtext'] =
    $config['longtext'] = $config['text'];

// blob
$config['tsvector'] = $config['tinyblob'] =
    $config['mediumblob'] = $config['longblob'] =
    $config['clob'] = $config['bytea'] =
    $config['blob'];

// CHARACTER
$config['character'] = $config['char'];
$config['character varying'] = $config['varchar'];

// TIME
$config['time without time zone'] = $config['time'];