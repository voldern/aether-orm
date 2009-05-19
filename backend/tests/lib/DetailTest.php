<?php // 
require_once('/home/lib/libDefines.lib.php');
require_once('PHPUnit/Framework.php');
require_once(PG_PATH . 'backend/lib/Detail.php');
require_once(LIB_PATH . 'Database.php');

class DetailTest extends PHPUnit_Framework_TestCase {
    public function testLoad() {
        $db = new Database('pg2_backend');
        $created = date('Y-m-d') . " 00:00:00";
        $title = 'Test';
        $db->queryf("INSERT INTO detail (created_at,modified_at,type,title,
            title_i18n) VALUES(?,?,?,?,?)",
            $created,$created,'int',$title,$title);
        $id = $db->getLastInsertId('id','detail');
        // Test
        $detail = new Detail($id);
        $this->assertEquals($created, $detail->get('createdAt'));
        $this->assertEquals($title, $detail->get('title'));
        $db->query("DELETE FROM detail WHERE id = $id");
    }

    public function testCreate() {
        $db = new Database('pg2_backend');
        $title = 'test';
        $detail = Detail::create($title,'int');
        $id = $detail->get('id');

        $row = $db->queryRow("SELECT * FROM detail WHERE id = $id");
        $this->assertEquals($title, $row['title']);
        $this->assertEquals('int', $row['type']);

        $db->query("DELETE FROM detail WHERE id = $id");
    }
}

?>
