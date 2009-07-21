<?php // 
require_once('/home/lib/libDefines.lib.php');
require_once(LIB_PATH . 'ActiveRecord.php');
/**
 * 
 * Interact with detail sets
 * 
 * Created: 2009-06-02
 * @author Raymond Julin
 * @package prisguide.backend.lib
 */


class DetailSet extends ActiveRecord {
    protected $id;
    protected $createdAt;
    protected $modifiedAt;
    protected $publishedAt;
    protected $deletedAt;
    protected $title;
    protected $titleI18N;

    public $tableInfo = array(
        'database' => 'pg2_backend',
        'table' => 'detail_set',
        'keys' => array(
            'id' => 'id'
        ),
        'indexes' => array(
            'id' => 'id',
            'deleted_at' => 'deletedAt'
        ),
        'fields' => array(
            'id' => 'id',
            'created_at' => 'createdAt',
            'modified_at' => 'modifiedAt',
            'published_at' => 'publishedAt',
            'deleted_at' => 'deletedAt',
            'title' => 'title',
            'title_i18n' => 'titleI18N',
        ),
        'relations' => array(
            'details' => array(
                'class' => 'Detail',
                'type' => 'many',
                'linker' => 'pg2_backend.detail_detail_set',
                'foreignKey' => 'detail_set_id',
                'linkerKey' => 'detail_id'
            )
        )
    );

    /**
     * Create a new set
     *
     * @return Detail
     * @param string $title
     * @param string $type
     */
    static public function create($title) {
        $db = new Database('pg2_backend');
        // Creation time is now obviously
        $created_at = date('Y-m-d H:i:s');
        $set = new DetailSet;
        $set->set('title', $title);
        $set->set('titleI18N', $title);
        $set->set('createdAt', $created_at);
        $set->set('modifiedAt', $created_at);
        $set->save();
        return $set;
    }

    /**
     * Persist record to database
     *
     * @access public
     * @return bool
     */
    public function save($idFromTable = 'detail_set') {
        parent::save($idFromTable);
    }
    /**
     * Connect/disconnect to detail
     *
     * @return bool
     * @param int $id
     */
    public function connectDetail($id) {
        $db = new Database($this->tableInfo['database']);
        $db->queryf("INSERT INTO detail_detail_set (detail_id,detail_set_id)
            VALUES(?,?)", $id, $this->get('id'));
    }
    public function disconnectDetail($id) {
        $db = new Database($this->tableInfo['database']);
        $db->queryf("DELETE FROM detail_detail_set WHERE 
            detail_id = ? AND detail_set_id = ?", $id, $this->get('id'));
    }
}
