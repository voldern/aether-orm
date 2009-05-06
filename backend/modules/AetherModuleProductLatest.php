<?php // 
require_once(PG_PATH . 'backend/lib/Manifestation.php');
/**
 * 
 * List the latest added products
 * 
 * Created: 2009-05-06
 * @author Raymond Julin
 * @package prisguide.backend.modules
 */

class AetherModuleProductLatest extends AetherModule {
    /**
     * Render module
     *
     * @access public
     * @return string
     */
    public function run() {
        $tpl = $this->sl->getTemplate();
        $config = $this->sl->get('aetherConfig');
        // Find works
        $works = RecordFinder::find('Work', array(
            'limit' => 25, 'order' => array(
                'created_at' => 'desc')
            )
        );
        var_dump($works);
        return $tpl->fetch('product/latest.tpl');
    }
}
?>
