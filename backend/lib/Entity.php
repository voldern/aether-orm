<?php

class PriceguideEntity extends ActiveRecord {
    private $id;
    private $created_at;
    private $replaced_by_entity_id;
    private $published_at;
    private $modified_at;
    private $deleted_at;
    private $title;

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