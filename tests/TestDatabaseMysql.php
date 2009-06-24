<?php

require_once('PHPUnit/Framework.php');
require_once('../Database.php');
require_once('../Config.php');
spl_autoload_register('Config::autoLoad');

class TestDatabaseMysql extends PHPUnit_Framework_TestCase {
    protected $db;
    protected $fixture;
    
    public function setUp() {
        $this->db = Database::instance('test_mysql');
        
        // Empty the test database
        if ($this->db->query('TRUNCATE TABLE test_database') === false)
            throw new Exception('Could not truncate test_database');

        // Insert test rows to the db
        $this->fixture = array(
            array('Espen', 'Volden', 18),
            array('Espen', 'S', 25),
            array('Edda', 'Media', 110),
            array('Ompa', 'Lompa', 5));


        foreach ($this->fixture as $row) {
            $sql = 'INSERT INTO test_database (first_name, sur_name, age)' .
                "VALUES ('{$row[0]}', '{$row[1]}', {$row[2]})";
            
            if ($this->db->query($sql) === false)
                throw new Exception('Cold not create fixture data');
        }

    }
    
    public function testQuerySelect() {
        $result = $this->db->query('SELECT first_name, sur_name, age '.
                                   'FROM test_database');
        $this->assertEquals($this->fixture,
                            $result->resultArray(false, MYSQL_NUM));
    }

    public function testQueryInsert() {
        $this->db->query("
                INSERT INTO test_database(first_name, sur_name, age)
                VALUES ('Jadda', 'Masa', 10)");
        $result = $this->db->query('
                SELECT first_name, sur_name, age FROM test_database
                ORDER BY id DESC LIMIT 1');
        
        $this->assertEquals('Jadda', $result[0]->first_name);
        $this->assertEquals('Masa', $result[0]->sur_name);
        $this->assertEquals('10', $result[0]->age);
    }

    public function testQueryUpdate() {
        $this->db->query("
                UPDATE test_database SET first_name = 'Dompa'
                WHERE first_name = 'Ompa'");
        $result = $this->db->query("
                SELECT first_name FROM test_database WHERE first_name = 'Dompa'
                LIMIt 1");
        $this->assertEquals('Dompa', $result[0]->first_name);
    }

    public function testQueryDelete() {
        $result = $this->db->query("
                DELETE FROM test_database WHERE first_name = 'Espen'");
        $this->assertEquals(2, $result->count());
        $this->assertEquals(2, count($result));

        $result = $this->db->query('SELECT id FROM test_database');
        $this->assertEquals(2, count($result));
        $this->assertEquals(2, $result->count());
    }

    public function testQBSelect() {
        $result = $this->db->select('first_name, sur_name, age')->
            get('test_database')->resultArray(false, MYSQL_NUM);
        $this->assertEquals($this->fixture, $result);

        $result = $this->db->select('first_name, sur_name, age')->
            from('test_database')->get()->resultArray(false, MYSQL_NUM);
        $this->assertEquals($this->fixture, $result);
    }

    public function testQBSelectWhere() {
        $result = $this->db->select('first_name, age')->
            where('sur_name', 'Media')->get('test_database')->
            resultArray(false, MYSQL_NUM);
        $this->assertEquals(array('Edda', 110), $result[0]);

        $result = $this->db->select('first_name, age')->
            getWhere('test_database', array('sur_name' => 'Media'))->
            resultArray(false, MYSQL_NUM);
        $this->assertEquals(array('Edda', 110), $result[0]);
    }

    public function testQBInsert() {
        $result = $this->db->insert('test_database', array(
                                        'first_name' => 'Jadda',
                                        'sur_name' => 'Masa',
                                        'age' => 10));
        $this->assertEquals(1, count($result));
        $this->assertEquals(1, $result->count());

        $result = $this->db->select('first_name', 'sur_name', 'age')->
            orderby('id', 'DESC')->get('test_database', 1);
        $this->assertEquals('Jadda', $result[0]->first_name);
        $this->assertEquals('Masa', $result[0]->sur_name);
        $this->assertEquals('10', $result[0]->age);

        /*
          Test using set() and from()
        */
        $this->db->set(array('first_name' => 'Jau', 'sur_name' => 'Jarn',
                             'age' => 15))->from('test_database')->insert();
        $result = $this->db->select('first_name', 'sur_name', 'age')->limit(1)->
            orderby('id', 'DESC')->get('test_database');
        $this->assertEquals('Jau', $result[0]->first_name);
        $this->assertEquals('Jarn', $result[0]->sur_name);
        $this->assertEquals('15', $result[0]->age);
    }

    public function testQBUpdate() {
        $result = $this->db->update('test_database',
                                    array('first_name' => 'Dompa'),
                                    array('first_name' => 'Ompa'));
        $this->assertEquals(1, count($result));
        $this->assertEquals(1, $result->count());

        $result = $this->db->select('first_name')->
            getwhere('test_database', array('first_name' => 'Dompa'));
        $this->assertEquals('Dompa', $result[0]->first_name);
    }

    public function testQBDelete() {
        $result = $this->db->delete('test_database', array('first_name' => 'Espen'));
        $this->assertEquals(2, $result->count());
        $this->assertEquals(2, count($result));

        $result = $this->db->select('id')->get('test_database');
        $this->assertEquals(2, count($result));
        $this->assertEquals(2, $result->count());
    }

    public function tearDown() {
        // Empty the test database
        $this->db->query('TRUNCATE TABLE test_database');
        $this->db = NULL;
    }
}
