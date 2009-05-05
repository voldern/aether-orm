<?php

require_once('/home/lib/libDefines.lib.php');
require_once(LIB_PATH . 'Database.php');
require_once(LIB_PATH . 'ActiveRecord.php');
require_once(PG_PATH . 'backend/lib/Entity.php');

class Work extends ActiveRecord {
    protected $id;
    protected $title;
    protected $createdAt;
    protected $replacesEntityId;
    protected $modifiedAt;
    protected $deletedAt;
    protected $publishedAt;

    public $tableInfo = array(
        'database' => 'pg2_backend',
        'table' => 'work_view',
        'keys' => array('id' => 'id'),
        'indexes' => array('id' => 'id'),
        'fields' => array(
            'id' => 'id',
            'title' => 'title',
            'created_at' => 'createdAt',
            'replaces_entity_id' => 'replacesEntityId',
            'modified_at' => 'modifiedAt',
            'deleted_at' => 'deletedAt',
            'published_at' => 'publishedAt'
            ),
        'relations' => array(
            'entity' => array(
                'class' => 'PriceguideEntity',
                'type' => 'one',
                'foreignKey' => 'id',
                'localKey' => 'id'
                )
            )
        );
    
    /**
     * Creates a new work and returns it
     *
     * @param $title string
     * @param $published_at string
     * @return work Work
     */
    static public function create($title, $published_at = null) {
        $db = new Database('pg2_backend');
        // Creation time is now obviously
        $created_at = date('Y-m-d');
        $id = $db->queryValuef("SELECT create_work(?, ?, ?)",
                                     $title, $created_at, $published_at);

        // We have all the information we need at this point.
        // The object now represents a row in the database
        $work = new Work;
        $work->set('id', $id);

        return $work;
    }

    /**
     * Persist record to database
     * Cannot use AR save function because its not possible
     * to write to views
     *
     * @access public
     * @return bool
     */
    public function save() {
        if ($this->dirty) {
            if ($this->tableInfoIsValid()) {
                // Hack to get the tableinfo from the entity class
                $info = new Entity;
                $info = $info->tableInfo;
                if (count($info['keys']) == 1 AND current($info['keys']) == 'id') {
                    $primary = $this->get(current($info['keys']));

                    if (is_numeric($primary)) {
                        $this->preUpdate();
                        $mode = "update";
                        $qb = new QueryBuilder('update');
                    }
                    else {
                        $this->preInsert();
                        $mode = "insert";
                        $qb = new QueryBuilder('insert');
                    }
                }
                else {
                    foreach ($info['keys'] as $inDb => $inHere) {
                        if (empty($this->$inHere)) {
                            throw new Exception("Tried saving with empty key [$inHere]");
                            return false;
                        }
                    }
                    $this->preInsert();
                    $mode = "insert";
                    $qb = new QueryBuilder('insert');
                    $qb->setOnDuplicateUpdate();
                }
                // Sets
                $qb->addFrom($info['table']);
                // Use keys to narrow result down to correct row
                if ($mode == 'update') {
                    foreach ($info['keys'] as $key => $var) {
                        if (!empty($this->$var))
                            $qb->addWhere($key, '=', $this->$var);
                    }
                }
                foreach ($info['fields'] as $key => $name) {
                    if ($name !== 'id' AND ($mode == 'update' OR 
                                            ($mode == 'insert' AND isset($this->$name)))) {
                        if ($this->$name !== null)
                            $qb->addSet($key, $this->$name);
                        else
                            $qb->addSet($key, 'NULL', NO_ESCAPE);
                    }
                }
                if ($mode == "update")
                    $qb->setLimit(1);
                $db = new Database($info['database']);
                $sql = $qb->build();
                $db->query($sql);
                // Keep track of queries used
                $this->queryLog($sql);
                /**
                 * Make sure id is set after an insert is done
                 * so this object is usable afterwards
                 */
                if ($mode == 'insert' && in_array('id', $info['fields'])) {
                    $this->set('id', $db->getLastInsertId());
                    $this->untaint();
                }
                $this->postSave();
                // Delete cache
                if ($this->id)
                    $this->deleteCache();
            }
        }
    }
}
