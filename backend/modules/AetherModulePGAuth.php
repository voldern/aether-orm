<?php //
/**
 *
 * Auth module
 *
 * Created: 2009-05-20
 * @author Espen Volden
 * @package prisguide.backend.modules
 */

class AetherModulePGAuth extends AetherModule {
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
        $loginRequired = $options['loginRequired'];

        // Check if the user is logged in
        if (isset($_SESSION['authInfo']) &&
            isset($_SESSION['authInfo']['verified']) &&
            $_SESSION['authInfo']['verified'] === true) {
            $loggedIn = true;
        }
        else
            $loggedIn = false;

        // If the user is required to be logged in but is not
        // redirect to the login page
        if ($loginRequired === 'true' && $loggedIn === false) {
            $referer = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('Location: /login?error=login_required&referer=' . $referer);
            die();
        }
        else
            $tpl->set('loggedIn', $loggedIn);
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
