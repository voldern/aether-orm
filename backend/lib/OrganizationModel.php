<?php // 
/**
 * 
 * Raymond Julin was too lazy to write a description and owes you a beer.
 * 
 * Created: 2009-07-31
 * @author Raymond Julin
 * @package
 */

class OrganizationModel extends AetherORM {
    protected $db = 'prisguide';
    protected $tableName = 'organization_view';
    protected $primaryKey = 'id';
    protected $columnAlias = array(
        'id' => 'id',
        'created_at' => 'createdAt',
        'replaced_by_entity_id' => 'replacedByEntityId',
        'modified_at' => 'modifiedAt',
        'deleted_at' => 'deletedAt',
        'published_at' => 'publishedAt',
        'title' => 'title',
    );

    /**
     * Create a new work
     *
     * @return Work
     * @param string $title
     * @param string $publishedAt
     */
    static public function create($title='',$publishedAt=null) {
        // Creation time is now obviously
        $createdAt = date('Y-m-d H:i:s');
        $organization = AetherORM::factory('organization');
        $organization->title = $title;
        $organization->createdAt = $createdAt;
        $organization->modifiedAt = $createdAt;
        $organization->publishedAt = $publishedAt;
        $organization->save();
        return $organization;
    }
}
