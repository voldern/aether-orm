<?php

require_once('/home/lib/libDefines.lib.php');
require_once('PHPUnit/Framework.php');
require_once(PG_PATH . 'backend/lib/Work.php');
require_once(LIB_PATH . 'Database.php');

class WorkTest extends PHPUnit_Framework_TestCase {

    /**
     * Test the PriceguideWork::create function
     */
    public function testCreateWork() {
        $db = new Database('pg2_backend');
        $work = new Work;
        $published_at = '2010-01-01';
        $created_at = date('Y-m-d');
        $title= ' GeForce 6600';
        $work = Work::create($title);
        $id = $work->get('id');

        $entity = $db->queryRowf('SELECT * FROM work_view WHERE id = ?', $id);
        $this->assertEquals($title, $entity['title']);
        $this->assertEquals($created_at, $entity['created_at']);
        $this->assertEquals($created_at, $entity['modified_at']);
        // Delete entry
        $db->query("DELETE FROM work WHERE entity_id = $id");
        $db->query("DELETE FROM entity WHERE id = $id");
    }
}
