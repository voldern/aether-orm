<?php // 
require_once('/home/lib/libDefines.lib.php');
require_once(LIB_PATH . 'ActiveRecord.php');
/**
 * 
 * Define a unit to describe details
 * 
 * Created: 2009-05-20
 * @author Raymond Julin
 * @package prisguide.backend.lib
 */

class Unit extends ActiveRecord {
    protected $id;
    protected $createdAt;
    protected $modifiedAt;
    protected $publishedAt;
    protected $deletedAt;

    protected $value;
    protected $calc;
    protected $setId;
    protected $title;
    protected $titleI18N;

    public $tableInfo = array(
        'database' => 'pg2_backend',
        'table' => 'unit',
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
            'unit_set_id' => 'setId',
            'title' => 'title',
            'title_i18n' => 'titleI18N',
            'value' => 'value',
            'calc' => 'calc',
        ),
        'relations' => array(
        )
    );

    /**
     * Create a new unit
     *
     * @return Unit
     * @param string $title
     * @param string $value
     */
    static public function create($title,$value) {
        // Creation time is now obviously
        $created_at = date('Y-m-d H:i:s');
        $unit = new Unit;
        if (is_object($value))
            $unit->set('calc', $value->expression);
        else
            $unit->set('value', $value);
        $unit->set('title', $title);
        $unit->set('createdAt', $created_at);
        $unit->set('modifiedAt', $created_at);
        $unit->save();
        return $unit;
    }
}
class UnitCalc {
    public $expression;
    public function __construct($expression) {
        $this->expression = $expression;
    }
}
?>
