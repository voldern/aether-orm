<?php // 
require_once('/home/lib/Autoload.php');
require_once('PHPUnit/Framework.php');

class DetailValueTest extends PHPUnit_Framework_TestCase {
    public function testLoad() {
        $db = new AetherDatabase('prisguide');
        $created = date('Y-m-d') . " 00:00:00";
        $ent = $db->get('entity');
        $det = $db->get('detail');
        $res = $db->insert("detail_value", array(
            'created_at' => $created,
            'modified_at' => $created,
            'entity_id' => $ent[0]->id,
            'detail_id' => $det[0]->id,
            'num_val' => 1,
            'status' => 'accepted'));
        $id = $res->insertId();
        // Test
        $detail = new DetailValueModel($id);
        $this->assertEquals($created, $detail->createdAt);
        $this->assertEquals(1, $detail->num);
        // Test get(value)
        $this->assertEquals(1, $detail->value);
        $db->delete("detail_value", array('id'=>$id));
    }

    public function testCreate() {
    /*
        $db = new Database('pg2_backend');
        $val = 1;
        $detail = DetailModel::create('test','int');
        $entity = Work::create('test');
        $dv = DetailValue::create($detail,$entity,$val);
        $id = $dv->get('id');

        $row = $db->queryRow("SELECT * FROM detail_value WHERE id = $id");
        $this->assertEquals($val, $row['num_val']);

        $db->delete("detail_value", array('id'=>$id));
        $db->query("DELETE FROM detail_value WHERE id = $id");
        $db->queryf("DELETE FROM detail WHERE id = ?", $detail->get('id'));
        $db->queryf("DELETE FROM entity WHERE id = ?", $entity->get('id'));
        $db->queryf("DELETE FROM work WHERE entity_id = ?", $entity->get('id'));
    */
    }

    public function testToArray() {
        /*
        $db = new Database('pg2_backend');
        $val = 1;
        $detail = Detail::create('test','int');
        $entity = Work::create('test');
        $dv = DetailValue::create($detail,$entity,$val);
        $id = $dv->get('id');

        $arr = $dv->toArray();

        $this->assertTrue(is_array($arr['detail']));

        $db->query("DELETE FROM detail_value WHERE id = $id");
        $db->queryf("DELETE FROM detail WHERE id = ?", $detail->get('id'));
        $db->queryf("DELETE FROM entity WHERE id = ?", $entity->get('id'));
        $db->queryf("DELETE FROM work WHERE entity_id = ?", $entity->get('id'));
        */
    }
}
