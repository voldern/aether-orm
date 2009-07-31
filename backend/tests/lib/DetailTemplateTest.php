<?php // 
require_once('/home/lib/Autoload.php');
require_once('PHPUnit/Framework.php');

class DetailTemplateTest extends PHPUnit_Framework_TestCase {
    public function testLoad() {
        $db = new Database('pg2_backend');
        $created = date('Y-m-d') . " 00:00:00";
        $title = 'Test';
        $db->queryf("INSERT INTO detail_template (created_at,modified_at,title,
            title_i18n) VALUES(?,?,?,?)",
            $created,$created,$title,$title);
        $id = $db->getLastInsertId('id','detail_template');
        // Test
        $set = new DetailTemplate($id);
        $this->assertEquals($title, $set->get('title'));
        $this->assertEquals($created, $set->get('createdAt'));
        $db->query("DELETE FROM detail_template WHERE id = $id");
    }

    public function testCreate() {
        $db = new Database('pg2_backend');
        $title = 'test';
        $set = DetailTemplate::create($title,'int');
        $id = $set->get('id');

        $row = $db->queryRow("SELECT * FROM detail_template WHERE id = $id");
        $this->assertEquals($title, $row['title']);

        $db->query("DELETE FROM detail_template WHERE id = $id");
    }
}
