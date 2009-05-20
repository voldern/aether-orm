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

        // Check if the user is logged in
        if (isset($_SESSION['authInfo']) &&
            isset($_SESSION['authInfo']['verified']) &&
            $_SESSION['authInfo']['verified'] === true) {
            $tpl->set('loggedIn', true);
        }
        else
            $tpl->set('loggedIn', false);
        
        return $tpl->fetch('header.tpl');
    }
}
?>
