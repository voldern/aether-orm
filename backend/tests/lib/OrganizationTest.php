<?php // 
require_once('/home/lib/libDefines.lib.php');
require_once('PHPUnit/Framework.php');
require_once(PG_PATH . 'backend/lib/Organization.php');
require_once(LIB_PATH . 'Database.php');
/**
 * 
 * Test Organizations
 * 
 * Created: 2009-05-28
 * @author Raymond Julin
 * @package prisguide.tests.lib
 */

class OrganizationTest extends PHPUnit_Framework_TestCase {

    /**
     * Test the Organization::create function
     */
    public function testCreateOrganization() {
        $db = new Database('pg2_backend');
        $title = 'Komplett.no';
        $org = Organization::create($title);
        $id = $org->get('id');

        $entity = $db->queryRowf(
            'SELECT * FROM organization_view WHERE id = ?',$id);

        $this->assertEquals($title, $entity['title']);
        // Delete entry
        $db->query("DELETE FROM entity WHERE id = $id");
        $db->query("DELETE FROM organization WHERE entity_id = $id");
    }

    public function testSaveOrganization() {
        $db = new Database('pg2_backend');
        $title = 'Komplett.no';
        $org = Organization::create($title);
        $id = $org->get('id');

        $t2 = 'Hei';
        $org->set('title', $t2);
        $org->save();

        $entity = $db->queryRowf(
            'SELECT * FROM organization_view WHERE id = ?',$id);
        $this->assertEquals($t2, $entity['title']);
        // Delete entry
        $db->query("DELETE FROM entity WHERE id = $id");
        $db->query("DELETE FROM organization WHERE entity_id = $id");
    }
}
?>
