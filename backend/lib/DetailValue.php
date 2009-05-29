<?php // 
require_once('/home/lib/libDefines.lib.php');
require_once(LIB_PATH . 'ActiveRecord.php');
require_once(PG_PATH . 'backend/lib/Detail.php');
/**
 * 
 * Raymond Julin was too lazy to write a description and owes you a beer.
 * 
 * Created: 2009-05-19
 * @author Raymond Julin
 * @package prisguide.backend.lib
 */

class DetailValue extends ActiveRecord {
    protected $id;
    protected $createdAt;
    protected $modifiedAt;
    protected $publishedAt;
    protected $deletedAt;

    protected $entityId;
    protected $detailId;
    protected $status;

    protected $num;
    protected $text;
    protected $date;
    protected $bool;

    protected $entity;
    protected $detail;

    public $tableInfo = array(
        'database' => 'pg2_backend',
        'table' => 'detail_value',
        'keys' => array(
            'id' => 'id'
        ),
        'indexes' => array(
            'id' => 'id',
            'entity_id' => 'entityId'
        ),
        'fields' => array(
            'id' => 'id',
            'created_at' => 'createdAt',
            'modified_at' => 'modifiedAt',
            'published_at' => 'publishedAt',
            'deleted_at' => 'deletedAt',
            'status' => 'status',
            'entity_id' => 'entityId',
            'detail_id' => 'detailId',
            'num_val' => 'num',
            'text_val' => 'text',
            'date_val' => 'date',
            'bool_val' => 'bool',
        ),
        'relations' => array(
            'detail' => array(
                'class' => 'Detail',
                'type' => 'one',
                'foreignKey' => 'id',
                'localKey' => 'detailId'
            ),
        )
    );

    /**
     * Persist record to database
     *
     * @access public
     * @return bool
     */
    public function save($idFromTable = 'detail_value') {
        parent::save($idFromTable);
    }
    
    /**
     * Override get to have some magic way to fetch "value"
     *
     * @return mixed
     * @param string $key
     */
    public function get($key) {
        if ($key == 'value') {
            switch ($this->get('detail')->get('type')) {
                case 'int':
                    return $this->get('num');
                case 'text':
                    return $this->get('text');
                case 'date':
                    return $this->get('date');
                case 'bool':
                    return $this->get('bool');
            }
        }
        else
            return parent::get($key);
    }

    /**
     * Set a new detail value
     *
     * @return DetailValue
     * @param Detail $detail
     * @param Entity $entity
     * @param mixed $value
     */
    static public function create(Detail $detail, $entity, $value) {
        $db = new Database('pg2_backend');
        // Creation time is now obviously
        $created_at = date('Y-m-d H:i:s');
        // Fgure out what value field to set
        $dv = new DetailValue;
        $dv->set('createdAt', $created_at);
        $dv->set('modifiedAt', $created_at);
        $dv->set('entityId', $entity->get('id'));
        $dv->set('detailId', $detail->get('id'));
        $dv->set('status', 'accepted');
        switch ($detail->get('type')) {
            case 'int':
                $dv->set('num', $value);
                break;
            case 'text':
                $dv->set('text', $value);
                break;
            case 'date':
                $dv->set('date', $value);
                break;
            case 'bool':
                $dv->set('bool', $value);
                break;
        }
        $dv->save();
        return $dv;
    }

    /**
     * Override default toArray() to include Details
     *
     * @access public
     * @return string
     * @param string $srcCharset
     */
    public function toArray($srcCharset = 'ISO-8859-1') {
        $detail = $this->get('detail');
        $data = array(
            'id' => $this->get('id'),
            'status' => $this->get('status'),
            'value' => $this->get('value'),
            'type' => $detail->get('type')
        );
        return $data;
    }
}
?>
