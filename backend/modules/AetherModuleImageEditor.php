<?php
require_once("/home/lib/Autoload.php");

/**
 * 
 * Simen Graaten was too lazy to write a description and owes you a beer.
 * 
 * Created: 2009-07-09
 * @author Simen Graaten
 * @package
 */

class AetherModuleImageEditor extends AetherModule {
    public function service($name) {
        if ($name == "showEditor") {
            $tpl = $this->sl->getTemplate();
            if (isset($_GET['selectedIds']) && strlen($_GET['selectedIds']) > 0) {
                $tpl->set('images', $this->loadImages($_GET['selectedIds']));
                $tpl->set('licenseTypes', ImageModel::$licenseTypes);
            }
            return new AetherTextResponse($tpl->fetch("image/editor.tpl"));
        }
        else if ($name == "saveImage") {
            $image = AetherORM::factory("Image", $_GET['imageId']);
            $image->title = $_GET['title'];
            $image->caption = $_GET['caption'];
            $image->photographer = $_GET['photographer'];
            $image->license = $_GET['license'];
            $image->save();
            return new AetherJSONResponse(array("status" => "ok"));
        }
        
        return new AetherTextResponse(__FILE__ . " says: Nothing to see here");
    }
    public function run() {
        return __FILE__ . " says: Nothing to see here";
    }

    private function loadImages($imageIds) {
        $images = AetherORM::factory("Image")->in("id", $imageIds);
        return $images->findAll();
    }
}
?>
