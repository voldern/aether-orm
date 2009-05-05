<?php

require_once('/home/lib/libDefines.lib.php');
require_once(LIB_PATH . 'Database.php');
require_once(LIB_PATH . 'ActiveRecord.php');
require_once(PG_PATH . 'backend/lib/Entity.php');

class Work extends ActiveRecord {
    protected $id;
    protected $title;
    protected $createdAt;
    protected $replacesEntityId;
    protected $modifiedAt;
    protected $deletedAt;
    protected $publishedAt;

    public $tableInfo = array(
        'database' => 'pg2_backend',
        'table' => 'work_view',
        'keys' => array('id' => 'id'),
        'indexes' => array('id' => 'id'),
        'fields' => array(
            'id' => 'id',
            'title' => 'title',
            'created_at' => 'createdAt',
            'replaces_entity_id' => 'replacesEntityId',
            'modified_at' => 'modifiedAt',
            'deleted_at' => 'deletedAt',
            'published_at' => 'publishedAt'
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
     * Creates a new work and returns it
     *
     * @param $title string
     * @param $published_at string
     * @return work Work
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

        return $work;
    }
}
