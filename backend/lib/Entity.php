<?php

class PriceguideEntity extends ActiveRecord {
    protected $id;
    protected $created_at;
    protected $replaced_by_entity_id;
    protected $published_at;
    protected $modified_at;
    protected $deleted_at;
    protected $title;

    public $tableInfo = array(
        'database' => 'pg2_backend',
        'table' => 'entity',
        'keys' => array('id' => 'id'),
        'indexes' => array('id' => 'id'),
        'fields' => array(
            'id' => 'id',
            'created_at' => 'createdAt',
            'replaced_by_entity_id' => 'replacedByEntityId',
            'published_at' => 'publishedAt',
            'modified_at' => 'modifiedAt',
            'deleted_at' => 'deletedAt',
            'title' => 'title'
            )
        );
}
