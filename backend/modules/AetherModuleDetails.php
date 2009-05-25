<?php // 
require_once(PG_PATH . 'backend/lib/Detail.php');
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
            case 'getDetail':
                $pid = false;
                if (isset($_GET['pid']))
                    $pid = $_GET['pid'];
                $response = $this->getDetail($_GET['id'],$pid);
                break;
            case 'getAllForEntity':
                $response = $this->getAllDetails($_GET['pid']);
                break;
        }

        return new AetherJSONResponse($response);
    }

    private function getDetail($id,$pid) {
        if (!is_numeric($id))
            return array('error'=>'Supplied ID is not numerical');
        $detail = new Detail($id);
        $resp = array('detail' => array(
            'id' => $detail->get('id'),
            'type' => $detail->get('type'),
            'title' => $detail->get('title'),
            'title_i18n' => $detail->get('titleI18N')
        ));
        if (is_numeric($pid)) {
            $col = RecordFinder::find('DetailValue',
                array('detailId'=>$id,'entityId'=>$pid));
            switch ($detail->get('type')) {
                case 'int':
                    $resp['value'] = $col->first->get('num');
                    break;
                case 'text':
                    $resp['value'] = $col->first->get('text');
                    break;
                case 'bool':
                    $resp['value'] = $col->first->get('bool');
                    break;
                case 'date':
                    $resp['value'] = $col->first->get('date');
                    break;
            }
        }
        return $resp;
    }
    private function getAllDetails($pid) {
        if (!is_numeric($pid))
            return array('error'=>'Supplied ID is not numerical');
        $resp = array();
        $col = RecordFinder::find('DetailValue',
            array('entityId'=>$pid));
        foreach ($col->getAll() as $r) {
            $arr = array();
            $detail = new Detail($r->get('detailId'));
            $arr['detail'] = array(
                'id' => $detail->get('id'),
                'type' => $detail->get('type'),
                'title' => $detail->get('title'),
                'title_i18n' => $detail->get('titleI18N')
            );
            switch ($detail->get('type')) {
                case 'int':
                    $arr['value'] = $r->get('num');
                    break;
                case 'text':
                    $arr['value'] = $r->get('text');
                    break;
                case 'bool':
                    $arr['value'] = $r->get('bool');
                    break;
                case 'date':
                    $arr['value'] = $r->get('date');
                    break;
            }
            $resp[$r->get('id')] = $arr;
        }
        return $resp;
    }
}