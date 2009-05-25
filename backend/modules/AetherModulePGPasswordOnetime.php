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
        $options = $config->getOptions();


        return $tpl->fetch('password/onetime.tpl');
    }
}
?>
