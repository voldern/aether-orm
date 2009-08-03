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
