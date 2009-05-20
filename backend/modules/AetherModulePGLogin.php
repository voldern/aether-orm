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
                // Redirect to front page
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

        if (isset($_GET['ssoAction']) && $_GET['ssoAction'] == 'logout')
            $tpl->set('logout', true);

        $tpl->set('loginURL', $this->options['loginURL']);
        return $tpl->fetch('login.tpl');
    }

    /**
     * Services provided for login/logout
     *
     * @param $name string
     * @return AetherJSONResponse
     */
    public function service($name) {
        //return new AetherJSONResponse(array());
    }
}
?>
