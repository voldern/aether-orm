<?php // 
require_once(PG_PATH . 'backend/lib/Manifestation.php');
/**
 * 
 * List the latest added products
 * 
 * Created: 2009-05-06
 * @author Raymond Julin
 * @package prisguide.backend.modules
 */

class AetherModuleProductLatest extends AetherModule {
    /**
     * Render module
     *
     * @access public
     * @return string
     */
    public function run() {
        $tpl = $this->sl->getTemplate();
        $config = $this->sl->get('aetherConfig');
        // Find works
        $works = RecordFinder::locate('Work', array(
            'deletedAt IS NULL',
            'limit' => 25, 'order' => array(
                'created_at' => 'desc')
            )
        );
        $wrks = array();
        foreach ($works->getAll() as $w) {
            $manis = array();
            try {
                $manifestations = $w->get('manifestations')
                    ->getBy('deletedAt',null)->getAll();
                if (count($manifestations) > 1) {
                    foreach ($manifestations as $m) {
                        $manis[] = array(
                            'id' => $m->get('id'),
                            'title' => $m->get('title'),
                            'created' => $m->get('createdAt'),
                        );
                    }
                }
            }
            catch (Exception $e) {
            }
            $wrks[] = array(
                'id' => $w->get('id'),
                'title' => $w->get('title'),
                'created' => $w->get('createdAt'),
                'manifestations' => $manis
            );
        }
        $tpl->set('works', $wrks);
        return $tpl->fetch('product/latest.tpl');
    }
}
?>
