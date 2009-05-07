<?php // vim:set ts=4 sw=4 et:

require_once(PG_PATH . 'backend/lib/Manifestation.php');
require_once(PG_PATH . 'backend/lib/Work.php');
require_once(LIB_PATH . 'ActiveRecord.php');

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
        if (isset($_GET['product_name']) && !empty($_GET['product_name'])) {
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

    /**
     *
     * @param $name string
     * @return AetherJSONResponse
     */
    public function service($name) {
        switch ($name) {
        case 'duplicateCheck':
            $response = $this->duplicateCheck($_GET['check']);
            break;
        default:
            $response = array('error' => 'Unknown action');
        }

        return new AetherJSONResponse($response);
    }
    
    /**
     * Check if a title exists
     * Returns json array usable by the dojo Duplicate module
     *
     * @param $title string
     * @return array
     */
    private function duplicateCheck($title) {
        $count = 0;
        $duplicates = array();
        if (empty($title))
            return array('duplicateCount' => 0, 'duplicates' => array());

        // Do a search for the product title in the database
        try {
            $collection = RecordFinder::find('Work', array('title' => $title));
            $count += $collection->count();

            foreach ($collection->getAll() as $row) {
                $duplicates[] = $row->get('title');
            }
        }
        catch (NoRecordsFoundException $e) {
        }
        
        try {
            $collection = RecordFinder::find('Manifestation', array('title' => $title));
            $count += $collection->count();

            foreach ($collection->getAll() as $row) {
                $duplicates[] = $row->get('title');
            }
        }
        catch (NoRecordsFoundException $e) {
        }
        
        return array('duplicateCount' => $count, 'duplicates' => $duplicates);
    }
}
?>
