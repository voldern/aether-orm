<?php
require_once('/home/lib/libDefines.lib.php');
require_once(LIB_PATH . 'ActiveRecord.php');

class EntityImage extends ActiveRecord {
    protected $id;
    protected $entityId;
    protected $imageId;
    protected $order;
    protected $createdAt;
    
    public $tableInfo = array(
        'database' => 'pg2_backend',
        'table' => 'entity_image',
        'keys' => array(
            'id' => 'id'
        ),
        'indexes' => array(
            'id' => 'id',
            'entity_id' => 'entityId',
            'image_id' => 'imageId'
        ),
        'fields' => array(
            'id' => 'id',
            'entity_id' => 'entityId',
            'image_id' => 'imageId',
            'order' => 'order',
            'created_at' => 'createdAt'
        ),
        'relations' => array(
        )
    );

    public function save($idFromTable = false) {
        if ($idFromTable == false)
            $idFromTable = $this->tableInfo['table'];

        $this->set('createdAt', date('c'));
        parent::save($idFromTable);
    }
}
