<?php
require_once('PHPUnit/Framework.php');
require_once("/home/lib/Autoload.php");

class ImageTest extends PHPUnit_Framework_TestCase {
    public function testSizeUrl() {
        $images = AetherORM::factory("Image")->in('id', array(29219))->findAll();
        $url200x200 = $images[0]->getSizeUrl(200, 200);
        $this->assertEquals($url200x200, "/29/29219/51.200x200!.jpg");

        $url400x300 = $images[0]->getSizeUrl(400);
        $this->assertEquals($url400x300, "/29/29219/51.400x300!.jpg");
    }

    public function testContainerUrl() {
        $images = AetherORM::factory("Image")->in('id', array(29219))->findAll();
        $urlContainer400x400 = $images[0]->getContainerUrl(400, 400);
        $this->assertEquals($urlContainer400x400, "/29/29219/51.400x300!.jpg");
    }

    /**
     * Trying to set an unavailable license type throws exception
     *
     * @expectedException InvalidArgumentException
     */
    public function testLicenseWrong() {
        $image = AetherORM::factory('Image', 29219);
        $image->license = "fail";
    }

    public function testLicenseCorrect() {
        $image = AetherORM::factory('Image', 29219);
        $image->license = "attribution";
    }
}
?>
