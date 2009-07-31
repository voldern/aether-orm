<?php // 
require_once('/home/lib/Autoload.php');
require_once('PHPUnit/Framework.php');
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
        $db = new AetherDatabase('prisguide');
        $title = 'Komplett.no';
        $org = OrganizationModel::create($title);
        $id = $org->id;

        $entity = $db->where('id',$id)->get('organization_view');

        $this->assertEquals($title, $entity[0]->title);
        // Delete entry
        $db->delete('entity',array('id'=>$id));
        $db->delete('organization',array('entity_id'=>$id));
    }

    public function testSaveOrganization() {
        $db = new AetherDatabase('prisguide');
        $title = 'Komplett.no';
        $org = OrganizationModel::create($title);
        $id = $org->id;

        $t2 = 'Hei';
        $org->title = $t2;
        $org->save();

        $entity = $db->where('id',$id)->get('organization_view');
        $this->assertEquals($t2, $entity[0]->title);
        // Delete entry
        $db->delete('entity',array('id'=>$id));
        $db->delete('organization',array('entity_id'=>$id));
    }
}
?>
