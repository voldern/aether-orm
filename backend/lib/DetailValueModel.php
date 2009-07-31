<?php // 
/**
 * 
 * Raymond Julin was too lazy to write a description and owes you a beer.
 * 
 * Created: 2009-07-29
 * @author Raymond Julin
 * @package
 */

class DetailValueModel extends AetherORM {
    protected $db = 'prisguide';
    protected $tableName = 'detail_value';
    protected $columnAlias = array(
        'id' => 'id',
        'created_at' => 'createdAt',
        'modified_at' => 'modifiedAt',
        'published_at' => 'publishedAt',
        'deleted_at' => 'deletedAt',
        'entity_id' => 'entityId',
        'detail_id' => 'detailId',
        'num_val' => 'num',
        'text_val' => 'text',
        'date_val' => 'date',
        'bool_val' => 'bool',
        'status' => 'status',
    );

    protected $belongsTo = array('detail','entity',);
    protected $foreignKey = array('detail' => 'detailId','entity'=>'entityId');

    /**
     * Set a new detail value
     *
     * @return DetailValue
     * @param Detail $detail
     * @param Entity $entity
     * @param mixed $value
     */
    static public function create(DetailModel $detail, $entity, $value) {
        $db = new AetherDatabase('prisguide');
        // Creation time is now obviously
        $created_at = date('Y-m-d H:i:s');
        // Fgure out what value field to set
        $dv = AetherORM::factory("DetailValue");
        $dv->createdAt = $created_at;
        $dv->modifiedAt = $created_at;
        $dv->entityId = $entity->id;
        $dv->detailId = $detail->id;
        $dv->status = 'accepted';
        switch ($detail->type) {
            case 'int':
                $dv->num = $value;
                break;
            case 'text':
                $dv->text = $value;
                break;
            case 'date':
                $dv->date = $value;
                break;
            case 'bool':
                $dv->bool = $value;
                break;
        }
        $dv->save();
        return $dv;
    }

    /**
     * Override get to have some magic way to fetch "value"
     *
     * @return mixed
     * @param string $key
     */
    public function __get($key) {
        if ($key == 'value') {
            switch ($this->detail->type) {
                case 'int':
                    return $this->num;
                case 'text':
                    return $this->text;
                case 'date':
                    return $this->date;
                case 'bool':
                    return $this->bool;
            }
        }
        else
            return parent::__get($key);
    }
}
?>
