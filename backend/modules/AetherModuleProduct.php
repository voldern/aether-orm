<?php // 
require_once(PG_PATH . 'backend/lib/Manifestation.php');
/**
 * 
 * Raymond Julin was too lazy to write a description and owes you a beer.
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
            $manifestation = new Manifestation($pid);
            $title = $manifestation->get('title');
            $tpl->set('title', $title);
        }
        return $tpl->fetch('product/product.tpl');
    }
}
?>
