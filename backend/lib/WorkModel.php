<?php

class WorkModel extends AetherORM {
    protected $db = 'prisguide';
    protected $tableName = 'work_view';
    protected $primaryKey = 'id';
    protected $columnAlias = array(
        'id' => 'id',
        'created_at' => 'createdAt',
        'replaced_by_entity_id' => 'replacedByEntityId',
        'modified_at' => 'modifiedAt',
        'deleted_at' => 'deletedAt',
        'published_at' => 'publishedAt',
        'title' => 'title',
    );

    protected $hasMany = array('manifestations');
    protected $foreignKey = array(
        'manifestations' => 'work_id'
    );

    /**
     * Create a new work
     *
     * @return Work
     * @param string $title
     * @param string $publishedAt
     */
    static public function create($title='',$publishedAt=null) {
        // Creation time is now obviously
        $createdAt = date('Y-m-d H:i:s');
        $work = AetherORM::factory('work');
        $work->title = $title;
        $work->createdAt = $createdAt;
        $work->modifiedAt = $createdAt;
        $work->publishedAt = $publishedAt;
        $work->save();
        return $work;
    }
}
