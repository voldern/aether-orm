<?php

class WorkModel extends AetherORM {
    protected $db = 'pg2_backend';
    protected $tableName = 'work_view';
    protected $columnAlias = array(
        'created_at' => 'createdAt',
        'replaced_by_entity_id' => 'replacedByEntityId',
        'modified_at' => 'modifiedAt',
        'deleted_at' => 'deletedAt',
        'published_at' => 'publishedAt'
        );

    protected $hasMany = array('manifestations');
    protected $foreignKey = array(
        'manifestations' => 'work_id'
        );
}