<?php

require_once('/home/lib/Autoload.php');
require_once('PHPUnit/Framework.php');
require_once(PG_PATH . 'backend/lib/LaunchDate.php');

class LaunchDateTest extends PHPUnit_Framework_TestCase {
    public function testPeriodHalf() {
        $this->assertEquals(0, 1);
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
    public function testSetIntervalQuarter() {
        $l = new LaunchDate();
        $l->setInterval(2008, "Q1");
        
        $this->assertEquals("2008-01-01", $l->get("startDate"));
        $this->assertEquals("2008-03-31", $l->get("endDate"));

        $l->setInterval(2008, "Q4");
        $this->assertEquals("2008-10-01", $l->get("startDate"));
        $this->assertEquals("2008-12-31", $l->get("endDate"));
    }
    public function testSetIntervalHalf() {
        $l = new LaunchDate();
        $l->setInterval(2008, "H2");
        
        $this->assertEquals("2008-07-01", $l->get("startDate"));
        $this->assertEquals("2008-12-31", $l->get("endDate"));
    }
    public function testSetIntervalYear() {
        $l = new LaunchDate();
        $l->setInterval(2008);
        
        $this->assertEquals("2008-01-01", $l->get("startDate"));
        $this->assertEquals("2008-12-31", $l->get("endDate"));
    }
    public function testSetIntervalMonth() {
        $l = new LaunchDate();

        $l->setInterval(2008, "04");
        $this->assertEquals("2008-04-01", $l->get("startDate"));
        $this->assertEquals("2008-04-30", $l->get("endDate"));

        $l->setInterval(2008, "12");
        $this->assertEquals("2008-12-01", $l->get("startDate"));
        $this->assertEquals("2008-12-31", $l->get("endDate"));

        $l->setInterval(2008, "12");
        $this->assertEquals("2008-12-01", $l->get("startDate"));
        $this->assertNotEquals("2008-12-30", $l->get("endDate"));
    }
}
