<?php // 

require_once('/home/lib/libDefines.lib.php');
require_once(LIB_PATH . 'ActiveRecord.php');
require_once(PG_PATH . 'backend/lib/Work.php');

/**
 * 
 * Manifestation
 * 
 * Created: 2009-05-04
 * @author Raymond Julin
 * @package aether.backend.lib
 */

class Manifestation extends ActiveRecord {
    protected $id;
    protected $workId;
    protected $title;
    protected $createdAt;
    protected $modifiedAt;
    protected $publishedAt;
    protected $deletedAt;
    protected $replacedBy;
    protected $neverEverCache = true;

    public $tableInfo = array(
        'database' => 'pg2_backend',
        'table' => 'manifestation_view',
        'keys' => array(
            'id' => 'id'
        ),
        'indexes' => array(
            'id' => 'id',
            'work_id' => 'workId',
            'title' => 'title',
            'deleted_at' => 'deletedAt'
        ),
        'fields' => array(
            'id' => 'id',
            'work_id' => 'workId',
            'title' => 'title',
            'created_at' => 'createdAt',
            'modified_at' => 'modifiedAt',
            'published_at' => 'publishedAt',
            'deleted_at' => 'deletedAt',
            'replaced_by_entity_id' => 'replacedBy'
        ),
        'relations' => array(
            'entity' => array(
                'class' => 'Entity',
                'type' => 'one',
                'foreignKey' => 'id',
                'localKey' => 'id'
            )
        )
    );
    
    /**
     * Create a new manifestation
     *
     * @return Manifestation
     * @param Work $work
     * @param string $title
     */
    static public function create(Work $work, $title=null,$published_at=null) {
        $db = new Database('pg2_backend');
        if (!$title)
            $title = $work->get('title');
        // Creation time is now obviously
        $created_at = date('Y-m-d H:i:s');
        $workId = $work->get('id');
        if ($workId) {
            $mani = new Manifestation;
            $mani->set('title', $title);
            $mani->set('workId', $workId);
            $mani->set('createdAt', $created_at);
            $mani->set('modifiedAt', $created_at);
            $mani->set('publishedAt', $published_at);
            $mani->save();
            return $mani;
        }
        else {
            throw new Exception("Work has no ID, halting");
        }
    }

    /**
     * Persist record to database
     *
     * @access public
     * @return bool
     */
    public function save($idFromTable = 'entity') {
        parent::save($idFromTable);
    }
}
?>
