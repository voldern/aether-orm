<?php // 
require_once('/home/lib/libDefines.lib.php');
require_once('PHPUnit/Framework.php');
require_once(PG_PATH . 'backend/lib/Unit.php');
require_once(LIB_PATH . 'Database.php');

class UnitTest extends PHPUnit_Framework_TestCase {
    public function testLoad() {
        $db = new Database('pg2_backend');
        $created = date('Y-m-d') . " 00:00:00";
        $title = 'Kilo';
        $db->queryf("INSERT INTO unit (created_at,modified_at,value, title,
            title_i18n) VALUES(?,?,?,?,?)",
            $created,$created,1,$title,$title);
        $id = $db->getLastInsertId('id','unit');
        // Test
        $detail = new Unit($id);
        $this->assertEquals($created, $detail->get('createdAt'));
        $this->assertEquals($title, $detail->get('title'));
        $db->query("DELETE FROM unit WHERE id = $id");
    }

    public function testCreate() {
        $db = new Database('pg2_backend');
        $title = 'Kilo';
        $unit = Unit::create($title,new UnitCalc('unit:1 * 1000'));
        $id = $unit->get('id');

        $row = $db->queryRow("SELECT * FROM unit WHERE id = $id");
        $this->assertEquals($title, $row['title']);

        $db->query("DELETE FROM unit WHERE id = $id");
    }
}

?>
