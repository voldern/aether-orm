<?php // 
/**
 * 
 * Raymond Julin was too lazy to write a description and owes you a beer.
 * 
 * Created: 2009-07-31
 * @author Raymond Julin
 * @package
 */

class ManifestationModel extends AetherORM {
    protected $db = 'prisguide';
    protected $tableName = 'manifestation_view';
    protected $primaryKey = 'id';
    protected $columnAlias = array(
        'id' => 'id',
        'created_at' => 'createdAt',
        'replaced_by_entity_id' => 'replacedByEntityId',
        'modified_at' => 'modifiedAt',
        'deleted_at' => 'deletedAt',
        'published_at' => 'publishedAt',
        'work_id' => 'workId',
        'title' => 'title',
        'replaced_by_entity_id' => 'replacedByEntityId'
    );

    protected $belongsTo = array('work');
    protected $foreignKey = array(
        'work' => 'workId'
    );

    /**
     * Create a new manifestation
     *
     * @return ManifestationModel
     * @param WorkModel $work
     * @param string $title
     * @param string $publishedAt
     */
    static public function create(WorkModel $work, $title=null,$publishedAt=null) {
        if ($work->id) {
            if (!$title)
                $title = $work->title;
            // Creation time is now obviously
            $createdAt = date('Y-m-d H:i:s');
            $manifestation = AetherORM::factory('manifestation');
            $manifestation->title = $title;
            $manifestation->createdAt = $createdAt;
            $manifestation->modifiedAt = $createdAt;
            $manifestation->publishedAt = $publishedAt;
            $manifestation->workId = $work->id;
            $manifestation->save();
            return $manifestation;
        }
        else {
            throw new Exception("Invalid WorkModel supplied");
        }
    }
}
