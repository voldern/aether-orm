<?php // 
require_once('/home/lib/Autoload.php');
require_once('PHPUnit/Framework.php');

class DetailSetTest extends PHPUnit_Framework_TestCase {
    public function testLoad() {
        $db = new AetherDatabase('prisguide');
        $created = date('Y-m-d') . " 00:00:00";
        $title = 'Test';
        $res = $db->insert("detail_set", array(
            'created_at' => $created,
            'modified_at' => $created,
            'title' => $title,
            'title_i18n' => $title));
        $id = $res->insertId();
        // Test
        $set = new DetailSetModel($id);
        $this->assertEquals($created, $set->createdAt);
        $this->assertEquals($title, $set->title);
        $db->delete("detail_set", array('id'=>$id));
    }

    public function testCreate() {
        $db = new AetherDatabase('prisguide');
        $title = 'test';
        $set = DetailSetModel::create($title,'int');
        $id = $set->id;

        $row = $db->where('id',$id)->get('detail_set');
        $this->assertEquals($title, $row[0]->title);

        $db->delete("detail_set", array('id'=>$id));
    }
}
