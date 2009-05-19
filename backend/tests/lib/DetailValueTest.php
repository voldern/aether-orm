<?php // 
require_once('/home/lib/libDefines.lib.php');
require_once('PHPUnit/Framework.php');
require_once(PG_PATH . 'backend/lib/DetailValue.php');
require_once(LIB_PATH . 'Database.php');

class DetailValueTest extends PHPUnit_Framework_TestCase {
    public function testInterface() {
        //$dv = DetailValue::get(array('entity_id'=>1)); // Set
        //$dv = DetailValue::get(array('entity_id'=>1)); // Set
    }
    public function testLoad() {
        $db = new Database('pg2_backend');
        $created = date('Y-m-d') . " 00:00:00";
        $db->queryf("INSERT INTO detail_value(created_at,modified_at,entity_id,
            detail_id,status,num_val) VALUES(?,?,?,?,?,?)",
            $created,$created,1,1,'accepted',1);
        $id = $db->getLastInsertId('id','detail_value');
        // Test
        $detail = new DetailValue($id);
        $this->assertEquals($created, $detail->get('createdAt'));
        $this->assertEquals(1, $detail->get('num'));
        $db->query("DELETE FROM detail_value WHERE id = $id");
    }

    public function testCreate() {
        $db = new Database('pg2_backend');
        $val = 1;
        $detail = Detail::create('test','int');
        $entity = Work::create('test');
        $dv = DetailValue::create($detail,$entity,$val);
        $id = $dv->get('id');

        $row = $db->queryRow("SELECT * FROM detail_value WHERE id = $id");
        $this->assertEquals($val, $row['num_val']);

        $db->query("DELETE FROM detail_value WHERE id = $id");
        $db->queryf("DELETE FROM detail WHERE id = ?", $detail->get('id'));
        $db->queryf("DELETE FROM entity WHERE id = ?", $entity->get('id'));
        $db->queryf("DELETE FROM work WHERE entity_id = ?", $entity->get('id'));
    }
}
