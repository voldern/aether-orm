<?php //
require_once(PG_PATH . 'backend/lib/PriceguideUser.php');
/**
 * 
 * Logout module
 * 
 * Created: 2009-05-20
 * @author Espen Volden
 * @package prisguide.backend.modules
 */

class AetherModulePGLogout extends AetherModule {
    /**
     * Render module
     *
     * @access public
     * @return string
     */
    public function run() {
        $config = $this->sl->get('aetherConfig');
                        
        // Logout the user and send him back to the login page
        $auth = new PriceguideUser;
        $auth->logout('http://' . $_SERVER['HTTP_HOST'] . '/login');
    }
}

?>