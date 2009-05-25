<?php //
require_once(PG_PATH . 'backend/lib/PriceguideUser.php');
/**
 *
 * Login module
 *
 * Created: 2009-05-19
 * @author Espen Volden
 * @package prisguide.backend.modules
 */

class AetherModulePGLogin extends AetherModule {
    /**
     * Render module
     *
     * @access public
     * @return string
     */
    public function run() {
        $tpl = $this->sl->getTemplate();
        $config = $this->sl->get('aetherConfig');

        // Check if the user currently logged in
        if (isset($_SESSION['authInfo']['verified']) &&
            $_SESSION['authInfo']['verified'] === true) {
            // Redirect to the page the user came from or the homepage
            if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']))
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            else
                header('Location: /');
            exit();
        }

        // Check if the user has logged in successfully
        if (isset($_GET['authId']) && !empty($_GET['authId']) &&
            strlen($_GET['authId']) == 32) {

            // Verify the authid
            $auth = new PriceguideUser;
            if ($auth->verify($_GET['authId']) === true) {

                // If the user is using a onetime password redirect
                // to the password reset page. If not try to redirect
                // to where the user came from or the frontpage as a last resort
                if (isset($_GET['authType']) && $_GET['authType'] == 'onetime_password')
                    header('Location: /password?message=onetime');
                elseif (isset($_GET['referer']) && !empty($_GET['referer']))
                    header('Location: ' . $_GET['referer']);
                else
                    header('Location: /');
                
                exit(0);
            }
            else {
                $error = 'verify_error';
            }
        }

        // Set error message
        if (isset($error) && !isset($_GET['error']))
            $tpl->set('error', $error);
        else
            $tpl->set('error', isset($_GET['error']) ? $_GET['error'] : '');

        // Check if we just came from a logout
        if (isset($_GET['ssoAction']) && $_GET['ssoAction'] == 'logout')
            $tpl->set('logout', true);

        // Set the referer if the auth module has included it during the redirect
        if (isset($_GET['referer']) && !empty($_GET['referer']))
            $tpl->set('referer', $_GET['referer']);

        if (isset($_GET['message']) && !empty($_GET['message']))
            $tpl->set('message', $_GET['message']);
        
        return $tpl->fetch('login.tpl');
    }
}
?>
