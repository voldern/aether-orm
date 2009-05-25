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

        // Make any errors accessable from the view
        if (isset($_GET['error']) && !empty($_GET['error']))
            $tpl->set('error', $_GET['error']);
        elseif (isset($_GET['ssoAction']) && $_GET['ssoAction'] == 'setPassword') {
            // Logout and redirect back to the login page
            header('Location: /logout?redirect=http://' . $_SERVER['HTTP_HOST'] .
                   '/login?message=password_changed');
            exit();
        }

        if (isset($_GET['message']) && !empty($_GET['message']))
            $tpl->set('message', $_GET['message']);
            
        $tpl->set('authId', $_SESSION['authInfo']['authId']);
        return $tpl->fetch('password.tpl');
    }
}
?>
