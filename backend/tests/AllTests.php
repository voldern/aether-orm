<?php // vim:set ts=4 sw=4 et:

require_once('/home/lib/libDefines.lib.php');
require_once(PG_PATH . 'backend/tests/lib/DetailTest.php');
require_once(PG_PATH . 'backend/tests/lib/DetailValueTest.php');
require_once(PG_PATH . 'backend/tests/lib/EntityTest.php');
require_once(PG_PATH . 'backend/tests/lib/ManifestationTest.php');
require_once(PG_PATH . 'backend/tests/lib/WorkTest.php');

/**
 * 
 * Run all PHPUnit test cases for prisguide
 * 
 * Created: 2009-06-01
 * @author Raymond Julin
 * @package aether.test
 */

class Framework_AllTests {
    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Aether Framework');
        $suite->addTestSuite('DetailTest');
        $suite->addTestSuite('DetailValueTest');
        $suite->addTestSuite('EntityTest');
        $suite->addTestSuite('ManifestationTest');
        $suite->addTestSuite('WorkTest');
        return $suite;
    }
}
?>
