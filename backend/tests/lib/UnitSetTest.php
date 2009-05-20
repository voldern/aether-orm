<?php // 
require_once('/home/lib/libDefines.lib.php');
require_once('PHPUnit/Framework.php');
require_once(PG_PATH . 'backend/lib/UnitSet.php');
require_once(LIB_PATH . 'Database.php');

class UnitTest extends PHPUnit_Framework_TestCase {
    public function testLoad() {
        $db = new Database('pg2_backend');
        $created = date('Y-m-d') . " 00:00:00";
        $title = 'Weight';
        $db->queryf("INSERT INTO unit_set (created_at,modified_at,title,
            title_i18n) VALUES(?,?,?,?)",
            $created,$created,$title,$title);
        $id = $db->getLastInsertId('id','unit_set');
        // Test
        $set = new UnitSet($id);
        $set->assure();
        $this->assertEquals($created, $set->get('createdAt'));
        $this->assertEquals($title, $set->get('title'));
        $db->query("DELETE FROM unit_set WHERE id = $id");
    }

    public function testCreate() {
        $db = new Database('pg2_backend');
        $title = 'Kilo';
        $unit = UnitSet::create($title);
        $id = $unit->get('id');

        $row = $db->queryRow("SELECT * FROM unit_set WHERE id = $id");
        $this->assertEquals($title, $row['title']);

        $db->query("DELETE FROM unit_set WHERE id = $id");
    }

    public function testCreateAndFillSet() {
        $db = new Database('pg2_backend');
        $title = 'Weight';
        $set = UnitSet::create($title);
        $first = Unit::create('Gram', 1);
        $id = $first->get('id');
        $second = Unit::create('Kilo', new UnitCalc("unit:$id * 1000"));
        $set->add($first)->add($second);

        $this->assertEquals(2, $set->count());

        $db->queryf("DELETE FROM unit_set WHERE id = ?", $set->get('id'));
        $db->queryf("DELETE FROM unit WHERE id = ?", $first->get('id'));
        $db->queryf("DELETE FROM unit WHERE id = ?", $second->get('id'));
    }
}

?>
