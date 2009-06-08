<?php // 
/**
 * 
 * Interface with details
 * 
 * Created: 2009-05-22
 * @author Raymond Julin
 * @package prisguide.backend.modules
 */

class AetherModuleDetails extends AetherModule {
    /**
     * Do nada
     */
    public function run() {
        return '';
    }

    /**
     * Services provided for a product

     * @param $name string
     * @return AetherJSONResponse
     */
    public function service($name) {
        switch ($name) {
            case 'Get':
                $pid = false;
                if (isset($_GET['pid']))
                    $pid = $_GET['pid'];
                $response = $this->getDetail($_GET['id'],$pid);
                break;
            case 'GetSets':
                $response = $this->getDetailSets($_GET);
                break;
            case 'GetSet':
                $response = $this->getSet($_GET);
                break;
            case 'GetSetDetails':
                break;
            case 'CreateSet':
                $response = $this->createSet($_GET);
                break;
            case 'SaveSet':
                $id = $_GET['id'];
                $response = $this->saveSet($id,$_POST);
                break;
            case 'SaveDetail':
                $id = $_GET['id'];
                $response = $this->saveDetail($id,$_POST);
                break;
            case 'AddDetail':
                $setId = $_GET['id'];
                $response = $this->addDetail($setId);
                break;
            default:
                $response = $this->error('Invalid service');
                break;
        }
        // Mix in request data sent
        $response['request'] = array(
            'get' => $_GET,
            'post' => $_POST);

        return new AetherJSONResponse($response);
    }
    
    /**
     * Wrap in an error message
     *
     * @return array
     * @param string $message
     */
    private function error($message) {
        return array('error' => array(
            'message' => $message));
    }
    
    /**
     * Send a succesfull statement
     *
     * @return array
     * @param string $message
     */
    private function success($message='') {
        $ret = array('success' => array());
        if ($message != '')
            $ret['success']['message'] = $message;
        return $ret;
            
    }

    private function getDetail($id,$pid) {
        if (!is_numeric($id))
            return $this->error('Supplied ID is not numerical');
        if (is_numeric($pid)) {
            $detail = new Detail($id);
            $col = RecordFinder::find('DetailValue',
                array('detailId'=>$id,'entityId'=>$pid));
            return $col->first->toArray();
        }
        else {
            return $this->error('Failed to load DetailValue');
        }
    }
    /**
     * List all detail sets as array
     *
     * @return array
     * @param array $data
     */
    private function getDetailSets($data) {
        $legalCriteria = array('active','title','limit');
        $criteria = array();
        if (isset($data['active'])) {
            if ($data['active'] == 1)
                $criteria[] = 'deletedAt IS NULL';
            else
                $criteria[] = 'deletedAt IS NOT NULL';
        }
        if (isset($data['limit']) AND is_numeric($data['limit'])) {
            $criteria['limit'] = $data['limit'];
        }
        $col = RecordFinder::find('DetailSet',$criteria);
        $col->setExportFields(array('details'));
        return $col->toArray();
    }

    /**
     * Get a set
     *
     * @return array
     * @param array $data
     */
    private function getSet($data) {
        if (isset($data['id']) AND is_numeric($data['id'])) {
            $set = new DetailSet($data['id']);
            $set->setExportFields(array('details'));
            if (isset($data['active']) AND $data['active'] == 1) {
                $array['records'] = Entity::removeDeletedArrayMembers(
                    $array['records']);
            }
            $array = $set->toArray();
            return $array;
        }
        else {
            return $this->error('Wrong ID supplied');
        }
    }
    
    /**
     * Save a detail set
     *
     * @return array
     * @param int $id
     * @param array $data
     */
    private function saveSet($id, $data) {
        if (!is_numeric($id))
            return $this->error('Supplied ID is not numerical');
        $set = new DetailSet($id);
        if (isset($data['set_name'])) {
            $name = trim($data['set_name']);
            $set->set('title', $name);
        }
        if (isset($data['set_name_i18n'])) {
            $name = trim($data['set_name_i18n']);
            $set->set('titleI18N', $name);
        }
        $set->save();
        return $this->success();
    }
    
    /**
     * Save a singular detail
     *
     * @return array
     * @param int $id
     * @param array $data
     */
    private function saveDetail($id, $data) {
        if (!is_numeric($id))
            return $this->error('Supplied ID is not numerical');
        $detail = new Detail($id);
        if (isset($data['title'])) {
            $title = trim($data['title']);
            $detail->set('title', $title);
        }
        if (isset($data['titleI18N'])) {
            $titleI18N = trim($data['titleI18N']);
            $detail->set('titleI18N', $titleI18N);
        }
        if (isset($data['type'])) {
            $type = trim($data['type']);
            $detail->set('type', $type);
        }
        $detail->save();
        return $this->success();
    }
    
    /**
     * Create a new detail
     *
     * @return array
     */
    private function addDetail($setId) {
        if (is_numeric($setId)) {
            $detail = Detail::create();
            $detail->save();
            $detail->connectSet($setId);
            $id = $detail->get('id');
            if (is_numeric($id))
                return $this->success("Detail [$id] created");
        }
        return $this->error('Failed to create Detail');
    }
}
