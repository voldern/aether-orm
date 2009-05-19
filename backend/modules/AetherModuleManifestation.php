<?php // 
require_once(PG_PATH . 'backend/lib/Manifestation.php');
/**
 * 
 * Interact with manifestations
 * 
 * Created: 2009-05-08
 * @author Raymond Julin
 * @package prisguide.backend.modules
 */

class AetherModuleManifestation extends AetherModule {
    /**
     * Render module
     *
     * @access public
     * @return string
     */
    public function run() {
    }

    /**
     * @param $name string
     * @return AetherJSONResponse
     */
    public function service($name) {
        switch ($name) {
            case 'Add':
                $response = $this->createManifestation($_GET);
                break;
            case 'Delete':
                $response = $this->deleteManifestation($_GET);
                break;
        }

        return new AetherJSONResponse($response);
    }
    private function createManifestation($data) {
        if (is_numeric($data['work_id'])) {
            $wid = $data['work_id'];
            $work = new Work($wid);
            $manifestation = Manifestation::create($work);
            return array('id'=>$manifestation->get('id'),'title'=>$manifestation->get('title'));
        }
    }
    private function deleteManifestation($data) {
        if (is_numeric($data['id'])) {
            $id = $data['id'];
            $manifestation = new Manifestation($id);
            $manifestation->delete();
            return array('id'=>$id);
        }
    }
}
?>
