<?php // 
require_once(PG_PATH . 'backend/lib/Detail.php');
require_once(PG_PATH . 'backend/lib/DetailSet.php');
require_once(PG_PATH . 'backend/lib/DetailValue.php');
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
        }

        return new AetherJSONResponse($response);
    }

    private function getDetail($id,$pid) {
        if (!is_numeric($id))
            return array('errors'=>
                array('message'=>'Supplied ID is not numerical')
            );
        if (is_numeric($pid)) {
            $detail = new Detail($id);
            $col = RecordFinder::find('DetailValue',
                array('detailId'=>$id,'entityId'=>$pid));
            return $col->first->toArray();
        }
        else {
            return array('errors'=>array(
                'message' => 'Failed to load DetailValue',)
            );
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
            return array('errors'=>
                array('message'=>'Wong ID supplied')
            );
        }
    }
}
