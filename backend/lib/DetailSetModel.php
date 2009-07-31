<?php

class DetailSetModel extends AetherORM {
    protected $db = 'prisguide';
    protected $tableName = 'detail_set';
    protected $columnAlias = array(
        'id' => 'id',
        'created_at' => 'createdAt',
        'modified_at' => 'modifiedAt',
        'published_at' => 'publishedAt',
        'deleted_at' => 'deletedAt',
        'title' => 'title',
        'title_i18n' => 'titleI18N'
    );

    protected $hasMany = array('details');
    protected $foreignKey = array(
        'details' => 'detail_set_id');

    /**
     * Create a new set
     *
     * @param string $title
     * @return DetailSetModel
     */
    static public function create($title) {
        $created_at = date('Y-m-d H:i:s');
        $set = AetherORM::factory('DetailSet');
        $set->title = $title;
        $set->titleI18N = $title;
        $set->createdAt = $created_at;
        $set->modifiedAt = $created_at;
        $set->save();

        return $set;
    }
}
