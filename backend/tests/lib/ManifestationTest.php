<?php

require_once('/home/lib/Autoload.php');
require_once('PHPUnit/Framework.php');

class ManifestationTest extends PHPUnit_Framework_TestCase {

    /**
     * Test the Manifestation::create function
     */
    public function testCreateManifestation() {
        $db = new AetherDatabase('prisguide');
        $title = 'GeForce 6600 512 MB';
        $work = WorkModel::create('GeForce 6600');
        $manifestation = ManifestationModel::create($work, $title);
        $id = $manifestation->id;
        $workId = $work->id;
        // Get work
        $this->assertEquals($workId, $manifestation->work->id);

        $entity = $db->where('id',$id)->get('manifestation_view');

        $this->assertEquals($title, $entity[0]->title);
        // Delete entry
        $db->delete('entity', array('id'=>$id));
        $db->delete('entity', array('id'=>$workId));
        $db->delete('manifestation', array('entity_id'=>$id));
        $db->delete('work', array('entity_id'=>$workId));
    }

    public function testSaveManifestation() {
        $db = new AetherDatabase('prisguide');
        $title = 'GeForce 6600 512 MB';
        $work = WorkModel::create('GeForce 6600');
        $manifestation = ManifestationModel::create($work, $title);
        $id = $manifestation->id;
        $workId = $work->id;


        $t2 = 'Hei';
        $manifestation->title = $t2;
        $manifestation->save();

        $entity = $db->where('id',$id)->get('manifestation_view');
        $this->assertEquals($t2, $entity[0]->title);
        // Delete entry
        $db->delete('entity', array('id'=>$id));
        $db->delete('entity', array('id'=>$workId));
        $db->delete('manifestation', array('entity_id'=>$id));
        $db->delete('work', array('entity_id'=>$workId));
    }
}
