<?php // 
require_once('/home/lib/libDefines.lib.php');
require_once(LIB_PATH . 'ActiveRecord.php');
require_once(PG_PATH . 'backend/lib/Unit.php');
/**
 * 
 * Set of units (weights for example)
 * 
 * Created: 2009-05-20
 * @author Raymond Julin
 * @package prisguide.backend.lib
 */

class UnitSet extends ActiveRecord {
    protected $id;
    protected $createdAt;
    protected $modifiedAt;
    protected $publishedAt;
    protected $deletedAt;

    protected $title;
    protected $titleI18N;

    public $tableInfo = array(
        'database' => 'pg2_backend',
        'table' => 'unit_set',
        'keys' => array(
            'id' => 'id'
        ),
        'indexes' => array(
            'id' => 'id',
        ),
        'fields' => array(
            'id' => 'id',
            'created_at' => 'createdAt',
            'modified_at' => 'modifiedAt',
            'published_at' => 'publishedAt',
            'deleted_at' => 'deletedAt',
            'title' => 'title',
            'title_i18n' => 'titleI18N',
        ),
        'relations' => array(
        )
    );

    /**
     * Create a new unitset
     *
     * @return UnitSet
     * @param string $title
     */
    static public function create($title) {
        // Creation time is now obviously
        $created_at = date('Y-m-d H:i:s');
        $unit = new UnitSet;
        $unit->set('title', $title);
        $unit->set('createdAt', $created_at);
        $unit->set('modifiedAt', $created_at);
        $unit->save();
        return $unit;
    }
    
    /**
     * Add a unit to this set
     *
     * @return UnitSet
     * @param Unit $unit
     */
    public function add(Unit $unit) {
        // Should assure record relation etc is loaded into space first
        // Connect to this set
        $unit->set('setId', $this->get('id'));
        $unit->save();
        $this->units[] = $unit;
        return $this;
    }
    
    /**
     * Count number of units
     *
     * @return int
     */
    public function count() {
        return count($this->units);
    }
}
?>
