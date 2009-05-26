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

        // Always assume that the user needs to be logged in by default
        if (isset($options['loginRequired']) &&
            in_array($options['loginRequired'], array('true', 'false')))
            $loginRequired = $options['loginRequired'];
        else
            $loginRequired = 'true';

        // Always assume that the user does _not_ need to be logged out by default
        if (isset($options['logoutRequired']) &&
            in_array($options['logoutRequired'], array('true', 'false')))
            $logoutRequired = $options['logoutRequired'];
        else
            $logoutRequired = 'false';
        
        // Check if the user is logged in
        if (isset($_SESSION['authInfo']) &&
            isset($_SESSION['authInfo']['verified']) &&
            $_SESSION['authInfo']['verified'] === true) {
            $loggedIn = true;
        }
        else
            $loggedIn = false;

        if ($loginRequired === 'true' && $logoutRequired === 'true')
            throw new Exception('Login and logout cannot both be required at the same time');
        
        // If the user is required to be logged in but is not
        // redirect to the login page
        if ($loginRequired === 'true' && $loggedIn === false) {
            $referer = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('Location: /login?error=login_required&referer=' . $referer);
            die();
        }
        elseif ($logoutRequired === 'true' && $loggedIn === true) {
            // If the user has to be logged out to access the page redirect him
            // where he came from
            if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']))
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            else
                header('Location: /');
            die();
        }
        
        $tpl->set('loggedIn', $loggedIn);
    }
}
?>
