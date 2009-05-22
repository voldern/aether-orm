<?php //
/**
 *
 * Password change module
 *
 * Created: 2009-05-22
 * @author Espen Volden
 * @package prisguide.backend.modules
 */

class AetherModulePGPassword extends AetherModule {
    /**
     * Render module
     *
     * @access public
     * @return string
     */
    public function run() {
        $tpl = $this->sl->getTemplate();
        $config = $this->sl->get('aetherConfig');
        $options = $config->getOptions();

        

        $tpl->set('authId', $_SESSION['authInfo']['authId']);
        $tpl->set('error', isset($error) ? $error : '');
        return $tpl->fetch('password.tpl');
    }
}
?>
