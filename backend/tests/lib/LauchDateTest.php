<?php

require_once('/home/lib/Autoload.php');
require_once('PHPUnit/Framework.php');
require_once(PG_PATH . 'backend/lib/LaunchDate.php');

class LaunchDateTest extends PHPUnit_Framework_TestCase {
    public function testPeriodHalf() {
        $l = new LaunchDate();

        $l->set('startDate', '2009-01-01');
        $l->set('endDate', '2009-06-30');
        $int = $l->getInterval();
        $this->assertEquals("H1", $int['period']);

        $l->set('endDate', '2009-12-31');
        $int = $l->getInterval();
        $this->assertNotEquals("H1", $int['period']);

        $l->set('startDate', '2009-07-01');
        $l->set('endDate', '2009-12-31');
        $int = $l->getInterval();
        $this->assertEquals("H2", $int['period']);
    }
    public function testPeriodQuarter() {
        $l = new LaunchDate();

        $l->set('startDate', '2009-01-01');
        $l->set('endDate', '2009-03-31');
        $int = $l->getInterval();
        $this->assertEquals("Q1", $int['period']);

        $l->set('endDate', '2009-03-30');
        $int = $l->getInterval();
        $this->assertNotEquals("Q1", $int['period']);
    }
    public function testPeriodMonth() {
        $l = new LaunchDate();

        $l->set('startDate', '2009-01-01');
        $l->set('endDate', '2009-01-31');
        $int = $l->getInterval();
        $this->assertEquals("01", $int['period']);

        $l->set('startDate', '2009-01-02');
        $int = $l->getInterval();
        $this->assertEquals(null, $int['period']);

        $l->set('startDate', '2009-04-01');
        $l->set('endDate', '2009-04-30');
        $int = $l->getInterval();
        $this->assertEquals("04", $int['period']);

        $l->set('startDate', '2009-04-01');
        $l->set('endDate', '2009-04-31');
        $int = $l->getInterval();
        $this->assertNotEquals("04", $int['period']);
    }
}
