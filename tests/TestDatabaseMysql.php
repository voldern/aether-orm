<?php

require_once('PHPUnit/Framework.php');
require_once('../Database.php');
require_once('../Config.php');
spl_autoload_register('Config::autoLoad');

class TestDatabaseMysql extends PHPUnit_Framework_TestCase {
    protected $db;
    
    public function setUp() {
        $this->db = Database::instance('test_mysql');
        
        // Empty the test database
        $this->db->query('TRUNCATE TABLE test_database');

        // Insert test rows to the db
        $fixture = array(
            array('Espen', 'Volden', 18),
            array('Espen', 'S', 25),
            array('Edda', 'Media', 110),
            array('Ompa', 'Lompa', 5));


        foreach ($fixture as $row) {
            $sql = 'INSERT INTO test_database (first_name, sur_name, age)' .
                "VALUES ('{$row[0]}', '{$row[1]}', {$row[2]})";
            
            if ($this->db->query($sql) === false)
                throw new Exception('Cold not create fixture data');
        }

    }
    
    public function testQuerySelect() {
    }

    public function tearDown() {
        // Empty the test database
        $this->db->query('TRUNCATE TABLE test_database');
        $this->db = NULL;
    }
}
