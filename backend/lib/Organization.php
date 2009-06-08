<?php // 
require_once('/home/lib/libDefines.lib.php');
require_once(PG_PATH . 'lib/Entity.php');
/**
 * 
 * Organization
 * 
 * Created: 2009-05-28
 * @author Raymond Julin
 * @package prisguide.backend.lib
 */

class Organization extends Entity {
    public $tableInfo = array(
        'database' => 'pg2_backend',
        'table' => 'organization_view',
        'keys' => array(
            'id' => 'id'
        ),
        'indexes' => array(
            'id' => 'id',
            'title' => 'title'
        ),
        'fields' => array(
            'id' => 'id',
            'title' => 'title',
            'created_at' => 'createdAt',
            'modified_at' => 'modifiedAt',
            'published_at' => 'publishedAt',
            'deleted_at' => 'deletedAt',
            'replaced_by_entity_id' => 'replacedByEntityId'
        ),
        'relations' => array(
            'entity' => array(
                'class' => 'Entity',
                'type' => 'one',
                'foreignKey' => 'id',
            ),
            'details' => array(
                'class' => 'DetailValue',
                'type' => 'many',
                'linker' => 'pg2_backend.detail_value',
                'foreignKey' => 'entity_id',
                'linkerKey' => 'id'
            ),
        )
    );
    
    /**
     * Creates a new Organization and returns it
     *
     * @param $title string
     * @param $published_at string
     * @return work Work
     */
    static public function create($title, $published_at = null) {
        $created_at = date('Y-m-d H:i:s');
        
        $work = new Organization;
        $work->set('title', $title);
        $work->set('createdAt', $created_at);
        $work->set('modifiedAt', $created_at);
        $work->set('publishedAt', $published_at);
        $work->save();
        
        return $work;
    }
}
?>
