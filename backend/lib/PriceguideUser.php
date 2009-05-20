<?php // 

require_once("/home/lib/libDefines.lib.php");
require_once(LIB_PATH . "user/AuthSystem.php");
require_once(LIB_PATH . "ActiveRecord.php");

/**
 * 
 * User object for all priceguide logins
 * Used for verification of authIds and holding session information.
 * 
 * Created: 2009-04-27
 * @author Simen Graaten
 * @package commonlibs
 */

class PriceguideUser extends ActiveRecord {
    private $auth;
    private $authInfo;
    private $verified = false;

    public function __construct() {
        // Supar sikrit key of doom
        $this->auth = new AuthSystem("2349cb749b425eeac8c1847cd4d2f4ba44d2a4c6");
        $this->authInfo = array();
    }

    /**
     * Verifies if an authId is a valid logged in session and sets some 
     * authInfo provided by the auth system.
     *
     * @param string $authId
     * @param string $referer
     * @return boolean
     */
    public function verify($authId, $referer = "/") {
        // Trust in the Session[tm] to bring some load off the auth-system.
        if (isset($_SESSION['authInfo']['verified']) && 
            $_SESSION['authInfo']['verified'] === true) {
            $this->verified = true;
        }

        if (!$this->verified) {
            $authVerification = $this->auth->verify($authId, $referer);

            if ($authVerification->verified == "true") {
                // Add more authInfo from auth system if needed
                $this->verified = true;
                $this->authInfo['verified'] = true;
                $this->authInfo['userId'] = (int)$authVerification->userId;
                $this->authInfo['authId'] = $authId;
                $_SESSION['authInfo'] = $this->authInfo;
            }
            else {
                $this->verified = false;
            }
        }

        return $this->verified;
    }

    /**
     * Log the user out and unset all authInfo.
     * NB: This function exits the current execution of the script and
     * redirects back to the $referer via auth.tek.no.
     *
     * @param string $referer
     */
    public function logout($referer = "/") {
        if (isset($_SESSION['authId']))
            $authId = $_SESSION['authId'];
        else
            $authId = NULL;

        if (isset($_SESSION['authInfo']))
            unset($_SESSION['authInfo']);

        $this->auth->logout($authId, $referer);
    }
}

?>
