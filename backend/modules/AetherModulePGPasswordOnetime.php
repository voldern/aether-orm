<?php //
/**
 *
 * Password onetime request module
 *
 * Created: 2009-05-25
 * @author Espen Volden
 * @package prisguide.backend.modules
 */

class AetherModulePGPasswordOnetime extends AetherModule {
    /**
     * Render module
     *
     * @access public
     * @return string
     */
    public function run() {
        $tpl = $this->sl->getTemplate();
        $config = $this->sl->get('aetherConfig');
        $this->options = $config->getOptions();

        if (isset($_POST['cellphone']) && !empty($_POST['cellphone'])) {
            $cellphone = $_POST['cellphone'];
            if (strlen($cellphone) != 10)
                $error = 'wrong_length';

            if (substr($cellphone, 0, 2) != '47')
                $error = 'wrong_prefix';

            if (!isset($error)) {
                if ($this->requestOnetime($cellphone) === true) {
                    // Redirect to the login page
                    header('Location: /login?message=onetime');
                    die();
                }
                else {
                    $error = 'system_error';
                }
            }
        }
        

        if (isset($error))
            $tpl->set('error', $error);
        
        return $tpl->fetch('password/onetime.tpl');
    }

    /**
     * Try to request a onetime password from the auth system
     * TODO: Make the function return usable error info
     *
     * @param $cellhpone
     * @return boolean
     */
    private function requestOnetime($cellphone) {
        $url = sprintf($this->options['loginURL'] . 'api/sms/onetime/?systemKey=%s' .
                       '&to=%d&from=PG2', '2349cb749b425eeac8c1847cd4d2f4ba44d2a4c6',
                       $cellphone);
        $url = $url . '&message=' . urlencode('Logg inn med %s');
        
        $response = simplexml_load_file($url);
        
        if ($response->status == 'success')
            return true;
        else
            return false;
    }
}
?>
