<?php
require_once('/home/lib/Autoload.php');
require_once('PHPUnit/Framework.php');
class EntityTest extends PHPUnit_Framework_TestCase {

    /**
     * Test the PriceguideWork::create function
     */
    public function testRemoveDeletedArrayMembers() {
        $array = array(
            'id' => 1,
            'title' => 'FOo',
            'deletedAt' => '',
            'manifestations' => array(
                'records' => array(
                1 => array(
                    'id' => 1,
                    'title' => 'FOo',
                    'deletedAt' => '',
                ),
                2 => array(
                    'id' => 1,
                    'title' => 'FOo',
                    'deletedAt' => '',
                ),
                3 => array(
                    'id' => 1,
                    'title' => 'FOo',
                    'deletedAt' => '2009-01-01 00:00:01',
                ),
                )
            )
        );
        $newArray = EntityModel::removeDeletedArrayMembers($array);
        $this->assertEquals(2, count($newArray['manifestations']['records']));
    }
}
