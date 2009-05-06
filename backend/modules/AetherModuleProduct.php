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
            $manifestations = $work->get('manifestations')->getAll();
            $tpl->set('manifestations', $manifestations);
            $tpl->set('id', $work->get('id'));
            $tpl->set('title', $work->get('title'));
        }
        return $tpl->fetch('product/product.tpl');
    }
}
?>
