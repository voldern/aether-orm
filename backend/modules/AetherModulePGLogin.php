<?php // 
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
