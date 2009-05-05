<?php

require_once('/home/lib/libDefines.lib.php');
require_once(LIB_PATH . 'Database.php');
require_once(LIB_PATH . 'ActiveRecord.php');
require_once(PG_PATH . 'backend/lib/Entity.php');

class Work extends ActiveRecord {
    protected $id;

    public $tableInfo = array(
        'database' => 'pg2_backend',
        'table' => 'work',
        'keys' => array('entity_id' => 'id'),
        'indexes' => array('entity_id' => 'id'),
        'fields' => array(
            'entity_id' => 'id',
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
     * Creates a new work and returns the entity_id
     *
     * @param $title string
     * @param $published_at string
     * @return id The id of the entity
     */
    static public function create($title, $published_at = null) {
        $db = new Database('pg2_backend');
        // Creation time is now obviously
        $created_at = date('Y-m-d');
        $id = $db->queryValuef("SELECT create_work(?, ?, ?)",
                                     $title, $created_at, $published_at);

        // We have all the information we need at this point.
        // The object now represents a row in the database
        $work = new Work;
        $work->set('id', $id);
        $work->untaint();

        return $work;
    }
}
