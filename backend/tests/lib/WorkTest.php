<?php

require_once('/home/lib/Autoload.php');
require_once('PHPUnit/Framework.php');

class WorkTest extends PHPUnit_Framework_TestCase {

    /**
     * Test the PriceguideWork::create function
     */
    public function testCreateWork() {
        $db = new AetherDatabase('prisguide');
        $published_at = '2010-01-01 10:11:12';
        $created_at = date('Y-m-d H:i:s');
        $title = 'GeForce 6600';
        
        $work = WorkModel::create($title, $published_at);
        $id = $work->id;

        //$entity = $db->queryRowf('SELECT * FROM work_view WHERE id = ?', $id);
        $entity = $db->where('id',$id)->get('work_view');

        $this->assertEquals($title, $entity[0]->title);
        $this->assertEquals($created_at, $entity[0]->created_at);
        $this->assertEquals($created_at, $entity[0]->modified_at);
        
        // Delete entry
        $db->delete('work', array('entity_id'=>$id));
        $db->delete('entity', array('id'=>$id));
    }

    public function testSaveWork() {
        $db = new AetherDatabase('prisguide');

        $created_at = date('Y-m-d H:i:s');
        $title = 'GeForce 6600';
        
        $work = WorkModel::create($title);
        $id = $work->id;

        $t2 = 'Hei';
        $work->title = $t2;
        $work->save();

        $entity = $db->where('id',$id)->get('work_view');
        $this->assertEquals($t2, $entity[0]->title);
        
        // Delete entry
        $db->delete('work', array('entity_id'=>$id));
        $db->delete('entity', array('id'=>$id));
    }
}
