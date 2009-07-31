<?php // 
require_once('/home/lib/Autoload.php');
require_once('PHPUnit/Framework.php');

class DetailFacadeTest extends PHPUnit_Framework_TestCase {
    public function testEnvironment() {
        $this->assertTrue(class_exists('DetailFacadeTest'));
    }

    public function testLoadListsOfConnections() {
        $fac = new DetailFacade;
        // Load lists of DetailSet
        $sets = $fac->load('DetailSet', 'all');
        $this->assertType('DetailFacade', $sets);
        /**
         * Load list of DetailSet with which set is selected
         * for this detail
         */
        $detail = new Detail(45);
        $sets = $fac->load('DetailSet', 'all')->selected($detail);
        $this->assertType('DetailFacade', $sets);
        $selCount = 0;
        foreach ($sets->toArray() as $a) {
            if ($a['_selected'] == true)
                $selCount++;
        }
        $this->assertGreaterThan(0, $selCount);
    }
}
