<?php

require_once('/home/lib/libDefines.lib.php');
require_once('PHPUnit/Framework.php');
require_once('../../lib/Work.php');
require_once(LIB_PATH . 'Database.php');

class PriceguideWorkTest extends PHPUnit_Framework_TestCase {

    /**
     * Test the PriceguideWork::create function
     */
    public function testCreateWork() {
        $db = new Database('pg2_backend');
        $work = new PriceguideWork;
        $id = $work->create('GeForce 6600', '2009-01-01', '2010-01-01');

        $entity = $db->queryRowf('SELECT * FROM entity WHERE id = ?', $id);
        $this->assertEquals('GeForce 6600', $entity['title']);
        $this->assertEquals('2009-01-01', $entity['created_at']);
        $this->assertEquals('2010-01-01', $entity['published_at']);
        $this->assertEquals('2009-01-01', $entity['modified_at']);
    }
}