<?php // 
/**
 * 
 * Render a simple header for prisguide backend
 * 
 * Created: 2009-04-29
 * @author Raymond Julin
 * @package prisguide.backend.modules
 */

class AetherModulePGHeader extends AetherModuleHeader {
    /**
     * Render module
     *
     * @access public
     * @return string
     */
    public function run() {
        $tpl = $this->sl->getTemplate();
        $tpl->set('title', 'Test');
        $this->applyCommonVariables($tpl);
        
        return $tpl->fetch('header.tpl');
    }
}
?>
