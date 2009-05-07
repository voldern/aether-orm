<?php // 
/**
 * 
 * Render footer
 * 
 * Created: 2009-04-29
 * @author Raymond Julin
 * @package prisguide.backend.modules
 */

class AetherModuleFooter extends AetherModule {
    /**
     * Render module
     *
     * @access public
     * @return string
     */
    public function run() {
        $tpl = $this->sl->getTemplate();
        return $tpl->fetch('footer.tpl');
    }
}
?>
