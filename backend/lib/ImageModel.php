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
        'image_original_source' => 'original',
        'image_license' => 'license'
    );

    protected $licenseTypes = array(
        'attribution',
        'attribution_share_alike',
        'attribution_no_derivatives',
        'attribution_non-commercial',
        'attribution_non-commercial_share_alike',
        'attribution_non-commercial_no_derivatives',
        'all_rights_reserved',
        'public_domain'
    );

    public function getSizeUrl($width, $height = false, $force = true) {
        $original = $this->original;
        $path = pathinfo($original);

        $ratio = $this->ratio;
        if (!$height) {
            if ($ratio > 0) {
                $height = round($width / $ratio);
            }
            else {
                return false;
            }
        }

        if ($force)
            $height .= "!";
        $url = sprintf("%s/%s.%sx%s.%s", $path['dirname'], $path['filename'],
                $width, $height, $path['extension']);
        return $url;
    }

    public function getContainerUrl($containerWidth, $containerHeight) {
        $containerRatio = $containerWidth / $containerHeight;

        if ($this->ratio > $containerRatio) {
            $imageWidth = $containerWidth;
            $imageHeight = round($containerWidth / $this->ratio, 0);
        }   
        else {  
            $imageWidth = round($containerHeight * $this->ratio, 0);
            $imageHeight = $containerHeight;
        }       
            
        return $this->getSizeUrl($imageWidth, $imageHeight);
    }
}

?>
