<?php // vim:set ts=4 sw=4 et:

require_once(PG_PATH . 'backend/lib/Manifestation.php');

/**
 * 
 * Make it possible to add a product (name)
 * 
 * Created: 2009-04-29
 * @author Raymond Julin
 * @package prisguide.backend.modules
 */

class AetherModuleProductAdd extends AetherModule {
    /**
     * Render module
     *
     * @access public
     * @return string
     */
    public function run() {
        $tpl = $this->sl->getTemplate();
        if (array_key_exists('product_name',$_GET) && !empty($_GET['product_name'])) {
            $title = $_GET['product_name'];
            if (is_numeric($_GET['variants']) AND $_GET['variants'] > 0) {
                $work = Work::create($title);
                for ($i = $_GET['variants']; $i > 0; $i--)
                    Manifestation::create($work, $title);
            }
            // If it was ok, redirect to product page
            if (is_numeric($work->get('id'))) {
                // Redirect
                $id = $work->get('id');
                header("Location: /products/$id");
            }
            else {
                $tpl->set('error', true);
                $tpl->set('title', $title);
            }

        }
        return $tpl->fetch('product/add.tpl');
    }
}
?>
