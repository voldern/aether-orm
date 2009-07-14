<?php

require_once('/home/lib/libDefines.lib.php');
require_once('PHPUnit/Framework.php');
require_once(PG_PATH . 'backend/lib/Manifestation.php');
require_once(LIB_PATH . 'Database.php');

class ManifestationTest extends PHPUnit_Framework_TestCase {

    /**
     * Test the Manifestation::create function
     */
    public function testCreateManifestation() {
        $db = new Database('pg2_backend');
        $title = 'GeForce 6600 512 MB';
        $work = Work::create('GeForce 6600');
        $manifestation = Manifestation::create($work, $title);
        $id = $manifestation->get('id');
        $workId = $work->get('id');
        // Get work
        $this->assertEquals($workId, $manifestation->get('work')->get('id'));

        $entity = $db->queryRowf(
            'SELECT * FROM manifestation_view WHERE id = ?',$id);

        $this->assertEquals($title, $entity['title']);
        // Delete entry
        $db->query("DELETE FROM entity WHERE id = $id");
        $db->query("DELETE FROM entity WHERE id = $workId");
        $db->query("DELETE FROM manifestation WHERE entity_id = $id");
        $db->query("DELETE FROM work WHERE entity_id = $workId");
    }

    public function testSaveManifestation() {
        $db = new Database('pg2_backend');
        $title = 'GeForce 6600 512 MB';
        $work = Work::create('GeForce 6600');
        $manifestation = Manifestation::create($work, $title);
        $id = $manifestation->get('id');
        $workId = $work->get('id');


        $t2 = 'Hei';
        $manifestation->set('title', $t2);
        $manifestation->save();

        $entity = $db->queryRowf(
            'SELECT * FROM manifestation_view WHERE id = ?',$id);
        $this->assertEquals($t2, $entity['title']);
        // Delete entry
        $db->query("DELETE FROM entity WHERE id = $id");
        $db->query("DELETE FROM entity WHERE id = $workId");
        $db->query("DELETE FROM manifestation WHERE entity_id = $id");
        $db->query("DELETE FROM work WHERE entity_id = $workId");
    }
}