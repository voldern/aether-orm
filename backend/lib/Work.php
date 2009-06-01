<?php

require_once('/home/lib/libDefines.lib.php');
require_once(PG_PATH . 'backend/lib/Entity.php');

class Work extends Entity {
    protected $manifestations;

    public $tableInfo = array(
        'database' => 'pg2_backend',
        'table' => 'work_view',
        'keys' => array('id' => 'id'),
        'indexes' => array(
            'id' => 'id',
            'title' => 'title',
            'deleted_at' => 'deletedAt',
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
            ),
            'manifestations' => array(
                'class' => 'Manifestation',
                'type' => 'many',
                'linker' => 'pg2_backend.manifestation_view',
                'foreignKey' => 'work_id',
                'linkerKey' => 'id'
            ),
            'details' => array(
                'class' => 'DetailValue',
                'type' => 'many',
                'linker' => 'pg2_backend.detail_value',
                'foreignKey' => 'entity_id',
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
     * Override default toArray() to include Manifestations
     *
     * @access public
     * @return string
     * @param string $srcCharset
     */
    public function toArray($srcCharset = 'ISO-8859-1') {
        $this->setExportFields(array('manifestations'),false);
        return parent::toArray();
        // mix in manifestations
        $ms = $this->get('manifestations')->getAll();
        $data['manifestations'] = array();
        foreach ($ms as $m) {
            $data['manifestations'][$m->get('id')] = $m->toArray();
        }
        return $data;
    }
}
