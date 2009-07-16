<?php // 

require_once("/home/lib/Autoload.php");
require_once(LIB_PATH . 'soap/client/NSS.php');
require_once(LIB_PATH . 'image/NewImage.php');
require_once('../lib/Search.php');

/**
 * 
 * Searches for products and/or articles
 * 
 * Created: 2009-05-04
 * @author Simen Graaten
 * @package pg2.backend
 */

class AetherModuleSearch extends AetherModule {
    /**
     * Search service:
     *   GET: service=qs
     *        module=Search
     *        query=te (part of keyword)
     *        limit=5 (limit)
     */
    public function service($name = 'ProductTitle') {
        $return = array();
        $options = $this->sl->get('aetherConfig')->getOptions();
        if (isset($_GET['query']) AND !empty($_GET['query'])) {
            if (isset($_GET['limit']) AND is_numeric($_GET['limit']))
                $limit = $_GET['limit'];
            else
                $limit = 5;
            // Perform search
            $string = trim($_GET['query']);
            if ($name == 'Article') {
                $res = Search::articleSearch($string);
                if ($res['data']['articles']) {
                    foreach($res['data']['articles'] as $row) {
                        $return[] = $row;
                    }
                }
            }
            if ($name == 'ProductTitle') {
                $res = Search::productSearch($string);
                if ($res['data']['products']) {
                    foreach($res['data']['products'] as $row) {
                        $return[] = $row;
                    }
                }
            }
        }

        return new AetherJSONResponse(array('return' => $return));
    }


    /**
     * This HAS to be implemented because its abstract
     * A bit stupid yes
     *
     * @access public
     * @return void
     */
    public function run() {
    }
}
?>
