<?php

require_once('/home/lib/libDefines.lib.php');
require_once(LIB_PATH . 'Database.php');
require_once(LIB_PATH . 'ActiveRecord.php');
require_once(PG_PATH . 'backend/lib/Entity.php');

class Work extends ActiveRecord {
    protected $id;
    protected $title;
    protected $createdAt;
    protected $replacedByEntityId;
    protected $modifiedAt;
    protected $deletedAt;
    protected $publishedAt;
    protected $manifestations;

    protected $neverEverCache = true;

    public $tableInfo = array(
        'database' => 'pg2_backend',
        'table' => 'work_view',
        'keys' => array('id' => 'id'),
        'indexes' => array(
            'id' => 'id',
            'title' => 'title'
            ),
        'fields' => array(
            'id' => 'id',
            'title' => 'title',
            'created_at' => 'createdAt',
            'replaced_by_entity_id' => 'replacedByEntityId',
            'modified_at' => 'modifiedAt',
            'deleted_at' => 'deletedAt',
            'published_at' => 'publishedAt'
        ),
        'relations' => array(
            'entity' => array(
                'class' => 'Entity',
                'type' => 'one',
                'foreignKey' => 'id',
                'localKey' => 'id'
            ),
            'manifestations' => array(
                'class' => 'Manifestation',
                'type' => 'many',
                'linker' => 'pg2_backend.manifestation_view',
                'foreignKey' => 'work_id',
                'linkerKey' => 'id'
            ),
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
        $created_at = date('Y-m-d H:i:s');
        
        $work = new Work;
        $work->set('title', $title);
        $work->set('createdAt', $created_at);
        $work->set('modifiedAt', $created_at);
        $work->set('publishedAt', $published_at);
        $work->save();
        
        return $work;
    }

    /**
     * Persist record to database
     *
     * @access public
     * @return bool
     */
    public function save($idFromTable = 'entity') {
        parent::save('entity');
    }
}
