<?php // 
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
        $works = AetherORM::factory('work')->where('deleted_at IS NOT NULL')
            ->findAll();
        // Find works
        $wrks = array();
        foreach ($works as $w) {
            $manis = array();
            foreach ($w->manifestations as $m) {
                if ($m->deletedAt == NULL)
                    echo 'fooo';
                $manis[] = array(
                    'id' => $m->id,
                    'title' => $m->title,
                    'created' => $m->createdAt,
                );
            }
            $wrks[] = array(
                'id' => $w->id,
                'title' => $w->title,
                'created' => $w->createdAt,
                'manifestations' => $manis
            );
        }
        $tpl->set('works', $wrks);
        return $tpl->fetch('product/latest.tpl');
    }
}
?>
