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
            case 'GetDetail':
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
            case 'GetDetails':
                $response = $this->getDetails($_GET);
                break;
            case 'SaveSet':
                $id = $_GET['id'];
                $response = $this->saveSet($id,$_POST);
                break;
            case 'Create':
                $name = $_GET['name'];
                $type = $_GET['type'];
                $response = $this->createResource($name, $type);
                break;
            case 'Delete':
                $id = $_GET['id'];
                $type = $_GET['type'];
                $response = $this->deleteResource($id, $type);
                break;
            case 'AddSet':
                $response = $this->addSet($_GET);
                break;
            case 'SaveDetail':
                $id = $_GET['id'];
                $response = $this->saveDetail($id,$_POST);
                break;
            case 'GetSetsFor':
                $id = $_GET['id'];
                $type = $_GET['type'];
                $response = $this->getSetsFor($type, $id);
                break;
            case 'GetDetailsFor':
                $id = $_GET['id'];
                $type = $_GET['type'];
                $response = $this->getDetailsFor($type, $id);
                break;
            default:
                $response = $this->error('Invalid service');
                break;
        }

        return new AetherJSONResponse($response);
    }
    
    /**
     * Wrap in an error message
     *
     * @return array
     * @param string $message
     */
    private function error($message,$data=array()) {
        return array('response' => array(
                'ok' => false,
                'message' => $message,
                'info' => $data
            ),
            'request' => array(
                'get' => $_GET,
                'post' => $_POST
            ),
        );
    }
    
    /**
     * Send a succesfull statement
     *
     * @return array
     * @param string $message
     */
    private function success($message='',$data=array()) {
        $ret = array('response' => array('ok'=>true));
        if ($message != '')
            $ret['message'] = $message;
        $ret['info'] = $data;
        $ret['request'] = array(
            'get' => $_GET,
            'post' => $_POST
        );
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
            $detail = new Detail($id);
            return $detail->toArray();
        }
    }
    /**
     * List all detail sets as array
     *
     * @return array
     * @param array $data
     */
    private function getDetailSets($data) {
        //$legalCriteria = array('active','title','limit');
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
        if (isset($data['details']))
            $col->setExportFields(array('details'));
        return $col->toArray();
    }
    /**
     * Return all details in system
     *
     * @return array
     * @param array $data
     */
    private function getDetails($data) {
        $legalCriteria = array('active','title','limit');
        $criteria = array();
        if (isset($data['active'])) {
            if ($data['active'] == 1)
                $criteria[] = 'deletedAt IS NULL';
            else
                $criteria[] = 'deletedAt IS NOT NULL';
        }
        $col = RecordFinder::find('Detail',$criteria);
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
     * Get all details for a certain type with id
     *
     * @return array
     * @param string $type
     * @param int $id
     */
    private function getDetailsFor($type,$id) {
        if ($type == 'set') {
            $field = 'detail_set_id';
            $sql = "SELECT id, title,detail_set_id FROM detail
                LEFT OUTER JOIN detail_detail_set
                ON detail.id = detail_detail_set.detail_id AND
                detail_detail_set.{$field} = $id";
            $db = new Database('pg2_backend');
            $res = $db->query($sql);
            $selected = array();
            $unselected = array();
            $sorters = array(
                'selName' => array(),
                'unselName' => array()
            );
            /**
             * Collect selected and unselected entries in respective
             * arrays that will be sorted by title later and
             * then joined into one array with the selected ones
             * at the top
             */
            foreach ($res as $k => $v) {
                if (is_numeric($v['detail_set_id'])) {
                    $res[$k]['selected'] = true;
                    $selected[$k] = $res[$k];
                    $sorters['selName'][$k] = $v['title'];
                }
                else {
                    $res[$k]['selected'] = false;
                    $unselected[$k] = $res[$k];
                    $sorters['unselName'][$k] = $v['title'];
                }
                unset($res[$k]['detail_set_id']);
            }
            array_multisort($sorters['selName'], SORT_ASC, $selected);
            array_multisort($sorters['unselName'], SORT_ASC, $unselected);
            return array_merge($selected, $unselected);
        }
        else {
            return $this->error("Not implemented");
        }
    }

    /**
     * Add resource
     *
     * @return array
     * @param string $name
     * @param string $type
     */
    private function createResource($name,$type) {
        if (strlen($name) >= 2) {
            $name = trim($name);
            switch ($type) {
                case 'set':
                    $resource = DetailSet::create($name);
                    $id = $resource->get('id');
                    break;
                case 'detail':
                    $resource = Detail::create($name);
                    $id = $resource->get('id');
                    break;
                case 'template':
                default:
                    return $this->error("Failed to create $type");
                    break;
            }
            return $this->success("Created $type [$id]",array('id'=>$id,'name'=>$name));
        }
        return $this->error("Cant create $type. To short");
    }

    /**
     * Delete resource
     *
     * @return array
     * @param int $id
     * @param string $type
     */
    private function deleteResource($id,$type) {
        if (is_numeric($id)) {
            switch ($type) {
                case 'set':
                    $resource = new DetailSet($id);
                    break;
                case 'detail':
                    $resource = new Detail($id);
                    break;
                case 'template':
                default:
                    return $this->error("Failed to delete $type::$id");
                    break;
            }
            if ($resource->delete())
                return $this->success("Deleted $type::$id",array('id'=>$id));
            else
                return $this->error("Failed to delete $type::$id for some unknown reason");
        }
        return $this->error("Cant delete $type without id");
    }
}
