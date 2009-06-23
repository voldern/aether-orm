<?php // 
/**
 * 
 * Interface with launch date
 * 
 * Created: 2009-06-15
 * @author Simen Graaten
 * @package prisguide.backend.modules
 */

class AetherModuleLaunchDate extends AetherModule {
    public function run() {
        $tpl = $this->sl->getTemplate();
        $cfg = $this->sl->get('aetherConfig');

        $eid = $cfg->getUrlVar('product_id');
        $tpl->set("entityId", $eid);

        $data = $this->getLaunchDate($eid);
        $startYear = date("Y") - 1;
        $years = array("");
        for ($d = $startYear; $d < $startYear + 5; $d++) {
            $years[] = $d;
        }
        $data['years'] = $years;
        $data['periods'] = array("", "Q1", "Q2", "Q3", "Q4", "H1", "H2", "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");

        $tpl->set("data", $data);
        return $tpl->fetch("product/launch_date.tpl");
    }

    /**
     * Services provided for a product
     *
     * @param $name string
     * @return AetherJSONResponse
     */
    public function service($name) {
        $eid = false;
        $response = array();

        if (isset($_GET['eid']))
            $eid = $_GET['eid'];

        switch ($name) {
            case 'Get':
                $response = $this->getLaunchDate($eid);
                break;
            case 'Save':
                $response = $this->saveLaunchDate($eid, $_POST);
                break;
            case 'Delete':
                $response = $this->deleteLaunchDate($eid);
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
    private function error($message) {
        return array('response' => array(
                'ok' => false,
                'message' => $message
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
    private function success($message='') {
        $ret = array('response' => array('ok'=>true));
        if ($message != '')
            $ret['message'] = $message;
        $ret['request'] = array(
            'get' => $_GET,
            'post' => $_POST
        );
        return $ret;
            
    }

    /**
     * Get launch date
     *
     * @return array
     * @param int $eid
     * @param array $data
     */
    private function getLaunchDate($eid) {
        try {
            $dates = RecordFinder::locate("LaunchDate", array("entityId = {$eid}"));
        } 
        catch (NoRecordsFoundException $e) {
            return array();
        }
        $ld = $dates->first;
        $ret = $ld->toArray();
        if ($ret['startDate'] == $ret['endDate']) {
            $ret['exactDate'] = substr($ret['startDate'], 0, 10);
        }
        else {
            $p = $ld->getInterval();
            $ret['year'] = $p['year'];
            $ret['period'] = $p['period'];
        }

        return $ret;
    }
    /**
     * Save launch date.  Prioritize exact_date
     *
     * @return array
     * @param array $data
     */
    private function saveLaunchDate($eid, $data) {
        if ($eid) {
            try {
                $dates = RecordFinder::locate("LaunchDate", array("entityId = {$eid}"));
                $ld = $dates->first;
            } 
            catch (NoRecordsFoundException $e) {
                $ld = new LaunchDate();
            }
            $ld->set('entityId', $eid);
        }
        else {
            $ld = new LaunchDate();
            $ld->save();
        }
        if (isset($data['exact_date']) && strlen($data['exact_date']) > 0) {
            $ld->set('startDate', $data['exact_date']);
            $ld->set('endDate', $data['exact_date']);
        }
        else if (isset($data['year']) && isset($data['period']) &&
                strlen($data['year']) > 0 && strlen($data['period']) > 0) {
            $ld->setInterval($data['year'], $data['period']);
        }
        
        if (!$data['year'] && !$date['period'] && !$data['exact_date']) {
            $ld->delete();
        }
        else {
            $ld->save();
        }
        return $this->success();
    }
    
    /**
     * Delete launch date
     *
     * @return array
     */
    private function deleteLaunchDate($eid) {
        if (is_numeric($eid)) {
            
            return $this->success("Launch date deleted");
        }
        return $this->error('Failed to delete launch date');
    }
}
