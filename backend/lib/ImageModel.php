<?php // 
/**
 * 
 * Image model for NewImage for working with AetherORM.  
 * TODO: Should probably be moved to commonlibs.
 * 
 * Created: 2009-07-08
 * @author Simen Graaten
 * @package
 */

class ImageModel extends AetherORM {
    protected $db = "image_backend";
    protected $tableName = "images";
    protected $columnAlias = array(
        'image_id' => 'id',
        'image_title' => 'title',
        'image_caption' => 'caption',
        'image_description' => 'description',
        'image_ratio' => 'ratio',
        'image_created' => 'created',
        'image_modified' => 'modified',
        'image_published_at' => 'publishedAt',
        'image_published' => 'published',
        'image_photographer' => 'photographer',
        'image_created_by' => 'createdBy',
        'image_modified_by' => 'modifiedBy',
        'image_original_source' => 'original'
    );
}

?>
