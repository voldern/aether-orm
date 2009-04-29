<?php

require_once('/home/lib/libDefines.lib.php');
require_once('PHPUnit/Framework.php');
require_once(PG_PATH . 'backend/lib/Work.php');
require_once(LIB_PATH . 'Database.php');

class PriceguideWorkTest extends PHPUnit_Framework_TestCase {

    /**
     * Test the PriceguideWork::create function
     */
    public function testCreateWork() {
        $db = new Database('pg2_backend');
        $work = new PriceguideWork;
        $published_at = '2010-01-01';
        $created_at = date('Y-m-d');
        $id = $work->create('GeForce 6600', $published_at);

        $entity = $db->queryRowf('SELECT * FROM entity WHERE id = ?', $id);
        $this->assertEquals('GeForce 6600', $entity['title']);
        $this->assertEquals($published_at, $entity['published_at']);
        $this->assertEquals($created_at, $entity['created_at']);
        $this->assertEquals($created_at, $entity['modified_at']);
        // Delete entry
        $db->query("DELETE FROM work WHERE entity_id = $id");
        $db->query("DELETE FROM entity WHERE id = $id");
    }
}
