<?php // 
require_once('/home/lib/libDefines.lib.php');
require_once(LIB_PATH . 'ActiveRecord.php');
/**
 * 
 * Detail facade
 * 
 * Created: 2009-05-19
 * @author Raymond Julin
 * @package aether.backend.lib
 */

class Detail extends ActiveRecord {
    protected $id;
    protected $createdAt;
    protected $modifiedAt;
    protected $publishedAt;
    protected $deletedAt;
    protected $title;
    protected $titleI18N;
    protected $type;

    public $tableInfo = array(
        'database' => 'pg2_backend',
        'table' => 'detail',
        'keys' => array(
            'id' => 'id'
        ),
        'indexes' => array(
            'id' => 'id',
        ),
        'fields' => array(
            'id' => 'id',
            'created_at' => 'createdAt',
            'modified_at' => 'modifiedAt',
            'published_at' => 'publishedAt',
            'deleted_at' => 'deletedAt',
            'type' => 'type',
            'title' => 'title',
            'title_i18n' => 'titleI18N',
        ),
        'relations' => array(
        )
    );

    /**
     * Create a new detail
     *
     * @return Detail
     * @param string $title
     * @param string $type
     */
    static public function create($title,$type) {
        $db = new Database('pg2_backend');
        // Creation time is now obviously
        $created_at = date('Y-m-d H:i:s');
        $detail = new Detail;
        $detail->set('title', $title);
        $detail->set('createdAt', $created_at);
        $detail->set('modifiedAt', $created_at);
        $detail->save();
        return $detail;
    }

    /**
     * Persist record to database
     *
     * @access public
     * @return bool
     */
    public function save($idFromTable = 'detail') {
        parent::save($idFromTable);
    }
}
?>
