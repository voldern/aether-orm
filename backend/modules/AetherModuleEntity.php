<?php // 
require_once(PG_PATH . 'backend/lib/Manifestation.php');
/**
 * 
 * Interact with all sorts of entities
 * 
 * Created: 2009-05-28
 * @author Raymond Julin
 * @package prisguide.backend.modules
 */

class AetherModuleEntity extends AetherModule {
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
                $response = $this->createEntity($_GET);
                break;
            case 'Delete':
                $response = $this->deleteEntity($_GET);
                break;
            case 'Get':
                $response = $this->getEntity($_GET);
                break;
            case 'GetByWork':
                $response = $this->getEntitiesByWork($_GET);
                break;
        }

        return new AetherJSONResponse($response);
    }
    private function createEntity($data) {
        switch (strtolower($data['type'])) {
            case 'manifestation':
                if (is_numeric($data['work_id'])) {
                    $wid = $data['work_id'];
                    $work = new Work($wid);
                    $manifestation = Manifestation::create($work);
                    return array('id'=>$manifestation->get('id'),'title'=>$manifestation->get('title'));
                }
                break;
        }
    }
    private function deleteEntity($data) {
        if (is_numeric($data['id'])) {
            $id = $data['id'];
            switch (strtolower($data['type'])) {
                case 'manifestation':
                    $object = new Manifestation($id);
                    break;
            }
            $object->delete();
            return array('id'=>$id);
        }
    }
    private function getEntity($data) {
        if (is_numeric($data['id'])) {
            $id = $data['id'];
            switch ($data['type']) {
                case 'manifestation':
                    $object = new Manifestation($id);
                    break;
            }
            return $object->toArray();
        }
    }
    private function getEntitiesByWork($data) {
        if (is_numeric($data['id'])) {
            $id = $data['id'];
            switch ($data['type']) {
                case 'manifestation':
                    $class = 'Manifestation';
                    break;
            }
            $ms = RecordFinder::locate($class, 
                array(
                    "workId = $id", 'deletedAt IS NULL',
                    'order' => array('title' => 'asc')
                )
            );
            return $ms->toArray();
        }
    }
}
?>
