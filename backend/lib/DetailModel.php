<?php

class DetailModel extends AetherORM {
    protected $db = 'pg2_backend';
    protected $tableName = 'detail';
    protected $columnAlias = array(
        'created_at' => 'createdAt',
        'modified_at' => 'modifiedAt',
        'published_at' => 'publishedAt',
        'deleted_at' => 'deletedAt',
        'title_i18n' => 'titleI18N'
        );


    /**
     * Create a new detail
     *
     * @return Detail
     * @param string $title
     * @param string $type
     */
    static public function create($title = '', $type = 'text') {
        $created_at = date('Y-m-d H:i:s');
        $detail = AetherORM::factory('detail');
        $detail->type = $type;
        $detail->title = $title;
        $detail->createdAt = $created_at;
        $detail->modifiedAt = $created_at;
        $detail->save();

        return $detail;
    }

    /**
     * Connect to set
     *
     * @param int $setId
     * @return bool
     */
    public function connectSet($setId) {
        $db = new AetherDatabase('pg2_backend');
        $status = $db->set(array('detail_id' => $this->id, 'detail_set_id' => $setId))
            ->insert('detail_detail_set');

        if (count($status) !== 1)
            throw new Exception("Could not connect $this->id to $setId");

        return true;
    }

    /**
     * Unlink from set
     *
     *
     * @param int $setId
     * @return bool
     */
    public function disconnectSet($setId) {
        $db = new AetherDatabase('pg2_backend');
        $status = $db->delete('detail_detail_set', array(
                                  'detail_id' => $this->id,
                                  'detail_set_id' => $setId));

        if (count($status) === 0)
            throw new Exception("No rows were delete");

        return true;
    }
}