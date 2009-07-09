<?php
require_once('/home/lib/Autoload.php');

/**
 * Handles linking of images to entities.
 */
class EntityImageModel extends AetherORM {
    protected $db = "pg2_backend";
    protected $tableName = "entity_image";
    protected $columnAlias = array(
            'id' => 'id',
            'entity_id' => 'entityId',
            'image_id' => 'imageId',
            'order' => 'order',
            'created_at' => 'createdAt'
    );

    public function save() {
        $this->createdAt = date('c');
        parent::save();
    }
}
