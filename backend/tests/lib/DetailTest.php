<?php // 
require_once('/home/lib/Autoload.php');
require_once('PHPUnit/Framework.php');


class DetailTest extends PHPUnit_Framework_TestCase {
    public function testLoad() {
        $db = new AetherDatabase('prisguide');
        $created = date('Y-m-d') . " 00:00:00";
        $title = 'Test';
        $res = $db->insert("detail", array(
            'created_at' => $created,
            'modified_at' => $created,
            'type' => 'int',
            'title' => $title,
            'title_i18n' => $title));
        $id = $res->insertId();
        // Test
        $detail = new DetailModel($id);
        $this->assertEquals($created, $detail->createdAt);
        $this->assertEquals($title, $detail->title);
        $db->delete("detail", array('id'=>$id));
    }

    public function testCreate() {
        $db = new AetherDatabase('prisguide');
        $title = 'test';
        $detail = DetailModel::create($title,'int');
        $id = $detail->id;

        $row = $db->where('id',$id)->get('detail');
        $this->assertEquals($title, $row[0]->title);
        $this->assertEquals('int', $row[0]->type);

        $db->delete("detail", array('id'=>$id));
    }
}

?>
