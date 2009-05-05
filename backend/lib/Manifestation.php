<?php // 

require_once('/home/lib/libDefines.lib.php');
require_once(LIB_PATH . 'Database.php');
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

    public $tableInfo = array(
        'database' => 'pg2_backend',
        'table' => 'manifestation_view',
        'keys' => array(
            'id' => 'id',
            'work_id' => 'work_id'
        ),
        'indexes' => array(
            'id' => 'id',
            'work_id' => 'work_id'
        ),
        'fields' => array(
            'id' => 'id',
            'work_id' => 'work_id'
        ),
        'relations' => array(
            'entity' => array(
                'class' => 'PriceguideEntity',
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
        if (!$published_at)
            $published_at = date('Y-m-d');
        // Creation time is now obviously
        $created_at = date('Y-m-d');
        $id = $work->get('id');
        if ($id) {
            $id = $db->queryValuef("SELECT create_manifestation(?,?,?,?)",
                $id, $title, $created_at, $published_at);

            // We have all the information we need at this point.
            // The object now represents a row in the database
            $object = new Manifestation;
            $object->set('id', $id);
            $object->untaint();

            return $object;
        }
        else {
            throw new Exception("Work has no ID, halting");
        }
    }
    
    /**
     * Disable save for now
     *
     * @return void
     */
    public function save() {
        throw new Exception("Saving is disabled");
    }
    
    /**
     * Disable delete
     *
     * @return void
     */
    public function delete() {
        throw new Exception("Deleting is disabled");
    }
}
?>
