<?php // 
require_once(PG_PATH . 'backend/lib/Manifestation.php');
/**
 * 
 * Show a single product for editing
 * 
 * Created: 2009-05-05
 * @author Raymond Julin
 * @package prisguide.backend.modules
 */

class AetherModuleProduct extends AetherModule {
    /**
     * Render module
     *
     * @access public
     * @return string
     */
    public function run() {
        $tpl = $this->sl->getTemplate();
        $config = $this->sl->get('aetherConfig');
        $pid = $config->getUrlVar('product_id');
        if (isset($pid) AND is_numeric($pid)) {
            $work = new Work($pid);
            // Get manifestations
            $tpl->set('title', $work->get('title'));
            $tpl->set('id', $pid);
        }
        return $tpl->fetch('product/product.tpl');
    }

    /**
     * Services provided for a product

     * @param $name string
     * @return AetherJSONResponse
     */
    public function service($name) {
        switch ($name) {
            case 'Save':
                $response = $this->saveTitles($_POST);
                break;
        }

        return new AetherJSONResponse($response);
    }

    private function saveTitles($data) {
        if (is_numeric($data['id'])) {
            $response = array();
            $pid = $data['id'];
            $work = new Work($pid);
            if ($data['blueprint'] != $work->get('title')) {
                $work->set('title', $data['blueprint']);
                $work->save();
                $response['blueprint_'.$pid] = $data['blueprint'];
            }

            // Get manifestations
            $manifestations = $work->get('manifestations');
            foreach ($data['mani'] as $key => $title) {
                $mani = $manifestations->byId($key);
                if ($title != $mani->get('title')) {
                    $mani->set('title', $title);
                    $mani->save();
                    $response[$key] = $title;
                }
            }
            return $response;
        }
        else
            return array(false);
    }
}
?>
