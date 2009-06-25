<?php
/**
 * [Object Relational Mapping][ref-orm] (ORM) is a method of abstracting database
 * access to standard PHP calls. All table rows are represented as model objects,
 * with object properties representing row data. ORM in Kohana generally follows
 * the [Active Record][ref-act] pattern.
 *
 * [ref-orm]: http://wikipedia.org/wiki/Object-relational_mapping
 * [ref-act]: http://wikipedia.org/wiki/Active_record
 *
 * @package ORM
 * @author Espen Volden
 */
class AetherORM {

    // Current relationships
    protected $hasOne = array();
    protected $belongsTo = array();
    protected $hasMany = array();
    protected $hasAndBelongsToMany = array();

    // Relationships that should always be joined
    protected $loadWith = array();

    // Current object
    protected $object  = array();
    protected $changed = array();
    protected $related = array();
    protected $loaded  = false;
    protected $saved   = false;
    protected $sorting;

    // Related objects
    protected $objectRelations = array();
    protected $changedRelations = array();

    // Model table information
    protected $objectName;
    protected $objectPlural;
    protected $tableName;
    protected $tableColumns;
    protected $ignoredColumns;

    // Table primary key and value
    protected $primaryKey = 'id';
    protected $primaryVal = 'name';

    // Array of foreign key name overloads
    protected $foreignKey = array();

    // Model configuration
    protected $tableNamesPlural = true;
    protected $reloadOnWakeup   = true;

    // Database configuration
    protected $db = 'default';
    protected $dbApplied = array();

    // With calls already applied
    protected $withApplied = array();

    // Stores column information for ORM models
    protected static $columnCache = array();

    /**
     * Creates and returns a new model.
     *
     * @param string $model model name
     * @param mixed $id parameter for find()
     * @return AetherORM
     */
    public static function factory($model, $id = NULL) {
        // Set class name
        $model = ucfirst($model) . 'Model';

        return new $model($id);
    }

    /**
     * Prepares the model database connection and loads the object.
     *
     * @param   mixed  parameter for find or object to load
     * @return  void
     */
    public function __construct($id = NULL) {
        // Set the object name and plural name
        $this->objectName = strtolower(substr(get_class($this), 0, -5));
        $this->objectPlural = Inflector::plural($this->objectName);

        if (!isset($this->sorting)) {
            // Default sorting
            $this->sorting = array($this->primaryKey => 'asc');
        }

        // Initialize database
        $this->__initialize();

        // Clear the object
        $this->clear();

        if (is_object($id)) {
            // Load an object
            $this->loadValues((array)$id);
        }
        elseif (!empty($id)) {
            // Find an object
            $this->find($id);
        }
    }

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return  void
     */
    public function __initialize() {
        if (!is_object($this->db)) {
            // Get database instance
            $this->db = Database::instance($this->db);
        }

        if (empty($this->tableName)) {
                // Table name is the same as the object name
                $this->tableName = $this->objectName;

                if ($this->tableNamesPlural === true) {
                    // Make the table name plural
                    $this->tableName = Inflector::plural($this->tableName);
                }
        }

        if (is_array($this->ignoredColumns)) {
            // Make the ignored columns mirrored = mirrored
            $this->ignoredColumns = array_combine($this->ignoredColumns,
                                                  $this->ignoredColumns);
        }

        // Load column information
        $this->reloadColumns();
    }

    /**
     * Allows serialization of only the object data and state, to prevent
     * "stale" objects being unserialized, which also requires less memory.
     *
     * @return  array
     */
    public function __sleep() {
        // Store only information about the object
        return array('objectName', 'object', 'changed', 'loaded', 'saved',
                     'sorting');
    }

    /**
     * Prepares the database connection and reloads the object.
     *
     * @return  void
     */
    public function __wakeup() {
        // Initialize database
        $this->__initialize();

        if ($this->reloadOnWakeup === true) {
                // Reload the object
                $this->reload();
        }
    }

    /**
     * Handles pass-through to database methods. Calls to query methods
     * (query, get, insert, update) are not allowed. Query builder methods
     * are chainable.
     *
     * @param string $method method name
     * @param array $args method arguments
     * @return mixed
     */
    public function __call($method, array $args) {
        if (method_exists($this->db, $method)) {
            if (in_array($method, array('query', 'get', 'insert', 'update', 'delete')))
                throw new Kohana_Exception('orm.query_methods_not_allowed');

            // Method has been applied to the database
            $this->dbApplied[$method] = $method;

            // Number of arguments passed
            $numArgs = count($args);

            if ($method === 'select' && $numArgs > 3) {
                // Call select() manually to avoid call_user_func_array
                $this->db->select($args);
            }
            else {
                // We use switch here to manually call the database methods. This is
                // done for speed: call_user_func_array can take over 300% longer to
                // make calls. Most database methods are 4 arguments or less, so this
                // avoids almost any calls to call_user_func_array.

                switch ($numArgs) {
                case 0:
                    if (in_array($method, array('openParen', 'closeParen',
                                                'enableCache', 'disableCache'))) {
                        // Should return AetherORM, not Database
                        $this->db->$method();
                    }
                    else {
                        // Support for things like reset_select, reset_write, list_tables
                        return $this->db->$method();
                    }
                    break;
                case 1:
                    $this->db->$method($args[0]);
                    break;
                case 2:
                    $this->db->$method($args[0], $args[1]);
                    break;
                case 3:
                    $this->db->$method($args[0], $args[1], $args[2]);
                    break;
                case 4:
                    $this->db->$method($args[0], $args[1], $args[2], $args[3]);
                    break;
                default:
                    // Here comes the snail...
                    call_user_func_array(array($this->db, $method), $args);
                    break;
                }
            }

            return $this;
        }
        else {
            throw new Exception('invalid method: ' . $method . ' in ' .
                                get_class($this));
        }
    }

    /**
     * Handles retrieval of all model values, relationships, and metadata.
     *
     * @param string $column column name
     * @return mixed
     */
    public function __get($column) {
        if (array_key_exists($column, $this->object)) {
                return $this->object[$column];
        }
        elseif (isset($this->related[$column])) {
                return $this->related[$column];
        }
        elseif ($column === 'primaryKeyValue') {
                return $this->object[$this->primaryKey];
        }
        elseif ($model = $this->relatedObject($column)) {
            // This handles the has_one and belongs_to relationships

            if (in_array($model->objectName, $this->belongsTo) ||
                !array_key_exists($this->foreignKey($column), $model->object)) {
                // Foreign key lies in this table
                //(this model belongs_to target model) OR an invalid has_one relationship
                $where = array($model->tableName .'.'. $model->primaryKey =>
                               $this->object[$this->foreignKey($column)]);
            }
            else {
                // Foreign key lies in the target table (this model has_one target model)
                $where = array($this->foreignKey($column, $model->tableName) =>
                               $this->primaryKeyValue);
            }

            // one<>alias:one relationship
            return $this->related[$column] = $model->find($where);
        }
        elseif (isset($this->hasMany[$column])) {
            // Load the "middle" model
            $through = AetherORM::factory(Inflector::singular(
                                              $this->hasMany[$column]));

            // Load the "end" model
            $model = AetherORM::factory(Inflector::singular($column));

            // Join ON target model's primary key set to 'through' model's foreign key
            // User-defined foreign keys must be defined in the 'through' model
            $joinTable = $through->tableName;
            $joinCol1  = $through->foreignKey($model->objectName, $joinTable);
            $joinCol2  = $model->tableName .'.'. $model->primaryKey;

            // one<>alias:many relationship
            return $this->related[$column] = $model
                ->join($joinTable, $joinCol1, $joinCol2)
                ->where($through->foreignKey($this->objectName, $joinTable),
                        $this->object[$this->primaryKey])->findAll();
        }
        elseif (in_array($column, $this->hasMany)) {
            // one<>many relationship
            $model = AetherORM::factory(Inflector::singular($column));
            return $this->related[$column] = $model
                ->where($this->foreignKey($column, $model->tableName),
                        $this->object[$this->primaryKey])->findAll();
        }
        elseif (in_array($column, $this->hasAndBelongsToMany)) {
            // Load the remote model, always singular
            $model = AetherORM::factory(Inflector::singular($column));

            if ($this->has($model, true)) {
                    // many<>many relationship
                    return $this->related[$column] = $model
                        ->in($model->tableName .'.'. $model->primaryKey,
                             $this->changedRelations[$column])->findAll();
            }
            else {
                // empty many<>many relationship
                return $this->related[$column] = $model
                    ->where($model->tableName .'.'. $model->primaryKey, NULL)
                    ->findAll();
            }
        }
        elseif (isset($this->ignoredColumns[$column])) {
            return NULL;
        }
        elseif (in_array($column, array(
                             'objectName', 'objectPlural', // Object
                             'primaryKey', 'primaryVal', 'tableName', 'tableColumns', // Table
                             'loaded', 'saved', // Status
                             'hasOne', 'belongsTo', 'hasMany', 'hasAndBelongsToMany',
                             'loadWith' // Relationships
                             ))) {
            // Model meta information
            return $this->$column;
        }
        else {
            throw new Exception('invalid property: ' . $column .' '.
                                get_class($this));
        }
    }

    /**
     * Handles setting of all model values, and tracks changes between values.
     *
     * @param string $column column name
     * @param mixed $value column value
     * @return void
     */
    public function __set($column, $value) {
        if (isset($this->ignoredColumns[$column])) {
            return NULL;
        }
        elseif (isset($this->object[$column]) || array_key_exists($column, $this->object)) {
            if (isset($this->tableColumns[$column])) {
                    // Data has changed
                    $this->changed[$column] = $column;
                    
                    // Object is no longer saved
                    $this->saved = false;
            }
            
            $this->object[$column] = $this->loadType($column, $value);
        }
        elseif (in_array($column, $this->hasAndBelongsToMany) && is_array($value)) {
                // Load relations
                $model = AetherORM::factory(Inflector::singular($column));

                if (!isset($this->objectRelations[$column])) {
                    // Load relations
                    $this->has($model);
                }

                // Change the relationships
                $this->changedRelations[$column] = $value;

                if (isset($this->related[$column])) {
                    // Force a reload of the relationships
                    unset($this->related[$column]);
                }
        }
        else {
            throw new Exception('invalid property: ' . $column .' '.
                                get_class($this));
        }
    }

    /**
     * Checks if object data is set.
     *
     * @param string $column column name
     * @return boolean
     */
    public function __isset($column) {
        return (isset($this->object[$column]) || isset($this->related[$column]));
    }

    /**
     * Unsets object data.
     *
     * @param string $column column name
     * @return void
     */
    public function __unset($column) {
        unset($this->object[$column], $this->changed[$column],
              $this->related[$column]);
    }

    /**
     * Displays the primary key of a model when it is converted to a string.
     *
     * @return  string
     */
    public function __toString() {
        return (string)$this->object[$this->primaryKey];
    }

    /**
     * Returns the values of this object as an array.
     *
     * @return  array
     */
    public function asArray() {
        $object = array();

        foreach ($this->object as $key => $val) {
            // Reconstruct the array (calls __get)
            $object[$key] = $this->$key;
        }

        return $object;
    }

    /**
     * Binds another one-to-one object to this model.  One-to-one objects
     * can be nested using 'object1:object2' syntax
     *
     * @param string $targetPath
     * @return void
     */
    public function with($targetPath) {
        if (isset($this->withApplied[$targetPath])) {
            // Don't join anything already joined
            return $this;
        }

        // Split object parts
        $objects = explode(':', $targetPath);
        $target  = $this;
        foreach ($objects as $object) {
            // Go down the line of objects to find the given target
            $parent = $target;
            $target = $parent->relatedObject($object);

            if (!$target) {
                // Can't find related object
                return $this;
            }
        }

        $targetName = $object;

        // Pop-off top object to get the parent object
        // (user:photo:tag becomes user:photo - the parent table prefix)
        array_pop($objects);
        $parentPath = implode(':', $objects);

        if (empty($parentPath)) {
            // Use this table name itself for the parent object
            $parentPath = $this->tableName;
        }
        else {
            if(!isset($this->withApplied[$parentPath])) {
                // If the parent object hasn't been joined yet,
                // do it first (otherwise LEFT JOINs fail)
                $this->with($parentPath);
            }
        }

        // Add to with_applied to prevent duplicate joins
        $this->withApplied[$targetPath] = true;

        // Use the keys of the empty object to determine the columns
        $select = array_keys($target->object);
        foreach ($select as $i => $column) {
            // Add the prefix so that load_result can determine the relationship
            $select[$i] = $targetPath .'.'. $column .' AS '.
                $targetPath .':'. $column;
        }


        // Select all of the prefixed keys in the object
        $this->db->select($select);

        if (in_array($target->objectName, $parent->belongsTo) ||
            !isset($target->object[$parent->foreignKey($targetName)])) {
            // Parent belongs_to target, use target's primary key as join column
            $joinCol1 = $target->foreignKey(true, $targetPath);
            $joinCol2 = $parent->foreignKey($targetName, $parentPath);
        }
        else {
            // Parent has_one target, use parent's primary key as join column
            $joinCol2 = $parent->foreignKey(true, $parentPath);
            $joinCol1 = $parent->foreignKey($targetName, $targetPath);
        }

        // This allows for models to use different table prefixes (sharing the same database)
        $joinTable =
            new AetherDatabaseExpression($target->db->tablePrefix() .
                                         $target->tableName .' AS ' .
                                         $this->db->tablePrefix() . $targetPath);
        
        // Join the related object into the result
        $this->db->join($joinTable, $joinCol1, $joinCol2, 'LEFT');

        return $this;
    }

    /**
     * Finds and loads a single database row into the object.
     *
     * @param mixed $id primary key or an array of clauses
     * @return AetherORM
     */
    public function find($id = NULL) {
        if ($id !== NULL) {
            if (is_array($id)) {
                // Search for all clauses
                $this->db->where($id);
            }
            else {
                // Search for a specific column
                $this->db->where($this->tableName .'.'.
                                 $this->uniqueKey($id), $id);
            }
        }

        return $this->loadResult();
    }

    /**
     * Finds multiple database rows and returns an iterator of the rows found.
     *
     * @param integer $limit SQL limit
     * @param integer $offset SQL offset
     * @return AetherORMIterator
     */
    public function findAll($limit = NULL, $offset = NULL) {
        if ($limit !== NULL && !isset($this->dbApplied['limit'])) {
            // Set limit
            $this->limit($limit);
        }

        if ($offset !== NULL && !isset($this->dbApplied['offset'])) {
            // Set offset
            $this->offset($offset);
        }

        return $this->loadResult(true);
    }

    /**
     * Creates a key/value array from all of the objects available. Uses find_all
     * to find the objects.
     *
     * @param string $key key column
     * @param string $val value column
     * @return array
     */
    public function selectList($key = NULL, $val = NULL) {
        if ($key === NULL)
            $key = $this->primaryKey;

        if ($val === NULL)
            $val = $this->primaryVal;

        // Return a select list from the results
        return $this->select($key, $val)->findAll()->selectList($key, $val);
    }

    /**
     * Validates the current object. This method should generally be called
     * via the model, after the $_POST Validation object has been created.
     *
     * @param object $array Validation array
     * @return boolean
     */
    /*
      TODO: Port validation
    public function validate(Validation $array, $save = false) {
        $safeArray = $array->safe_array();

        if (!$array->submitted()) {
            foreach ($safeArray as $key => $value) {
                // Get the value from this object
                $value = $this->$key;
                
                if (is_object($value) && $value instanceof ORMIterator) {
                    // Convert the value to an array of primary keys
                    $value = $value->primaryKeyArray();
                }
                
                // Pre-fill data
                $array[$key] = $value;
            }
        }

        // Validate the array
        if ($status = $array->validate()) {
            // Grab only set fields (excludes missing data, unlike safe_array)
            $fields = $array->as_array();

            foreach ($fields as $key => $value) {
                if (isset($safe_array[$key])) {
                    // Set new data, ignoring any missing fields or fields without rules
                    $this->$key = $value;
                }
            }

            if ($save === true || is_string($save)) {
                // Save this object
                $this->save();

                if (is_string($save)) {
                    // Redirect to the saved page
                    url::redirect($save);
                }
            }
        }

        // Return validation status
        return $status;
    }
    */

    /**
     * Saves the current object.
     *
     * @return AetherORM
     */
    public function save() {
        if (!empty($this->changed)) {
            $data = array();
            foreach ($this->changed as $column) {
                // Compile changed data
                $data[$column] = $this->object[$column];
            }

            if ($this->loaded === true) {
                $query = $this->db
                    ->where($this->primaryKey, $this->object[$this->primaryKey])
                    ->update($this->tableName, $data);

                // Object has been saved
                $this->saved = true;
            }
            else {
                $query = $this->db->insert($this->tableName, $data);
                if ($query->count() > 0) {
                    if (empty($this->object[$this->primaryKey])) {
                        // Load the insert id as the primary key
                        $this->object[$this->primaryKey] = $query->insertId();
                    }
                    
                    // Object is now loaded and saved
                    $this->loaded = $this->saved = true;
                }
            }
            
            if ($this->saved === true) {
                // All changes have been saved
                $this->changed = array();
            }
        }

        if ($this->saved === true && !empty($this->changedRelations)) {
            foreach ($this->changedRelations as $column => $values) {
                // All values that were added
                $added = array_diff($values, $this->objectRelations[$column]);

                // All values that were saved
                $removed = array_diff($this->objectRelations[$column], $values);

                if (empty($added) && empty($removed)) {
                    // No need to bother
                    continue;
                }

                // Clear related columns
                unset($this->related[$column]);

                // Load the model
                $model = AetherORM::factory(Inflector::singular($column));

                if (($join_table =
                     array_search($column, $this->hasAndBelongsToMany)) === false)
                    continue;

                if (is_int($joinTable)) {
                    // No "through" table, load the default JOIN table
                    $joinTable = $model->joinTable($this->tableName);
                }

                // Foreign keys for the join table
                $objectFk  = $this->foreignKey(NULL);
                $relatedFk = $model->foreignKey(NULL);

                if (!empty($added)) {
                    foreach ($added as $id) {
                        // Insert the new relationship
                        $this->db->insert($joinTable, array(
                                              $objectFk  => $this->object[$this->primaryKey],
                                              $relatedFk => $id));
                    }
                }

                if (!empty($removed)) {
                    $this->db
                        ->where($objectFk, $this->object[$this->primaryKey])
                        ->in($relatedFk, $removed)
                        ->delete($joinTable);
                }

                // Clear all relations for this column
                unset($this->objectRelations[$column],
                      $this->changedRelations[$column]);
            }
        }

        return $this;
    }

    /**
     * Deletes the current object from the database. This does NOT destroy
     * relationships that have been created with other objects.
     *
     * @return AetherORM
     */
    public function delete($id = NULL) {
        if ($id === NULL && $this->loaded) {
            // Use the the primary key value
            $id = $this->object[$this->primaryKey];
        }

        // Delete this object
        $this->db->where($this->primaryKey, $id)->delete($this->tableName);

        return $this->clear();
    }

    /**
     * Delete all objects in the associated table. This does NOT destroy
     * relationships that have been created with other objects.
     *
     * @param array $ids ids to delete
     * @return AetherORM
     */
    public function deleteAll($ids = NULL) {
        if (is_array($ids)) {
            // Delete only given ids
            $this->db->in($this->primaryKey, $ids);
        }
        elseif (is_null($ids)) {
            // Delete all records
            $this->db->where('1=1');
        }
        else {
            // Do nothing - safeguard
            return $this;
        }

        // Delete all objects
        $this->db->delete($this->tableName);

        return $this->clear();
    }

    /**
     * Unloads the current object and clears the status.
     *
     * @return AetherORM
     */
    public function clear() {
        // Create an array with all the columns set to NULL
        $columns = array_keys($this->tableColumns);
        $values  = array_combine($columns, array_fill(0, count($columns), NULL));

        // Replace the current object with an empty one
        $this->loadValues($values);

        return $this;
    }

    /**
     * Reloads the current object from the database.
     *
     * @return AetherORM
     */
    public function reload() {
        return $this->find($this->object[$this->primaryKey]);
    }

    /**
     * Reload column definitions.
     *
     * @param boolean $force force reloading
     * @return AetherORM
     */
    public function reloadColumns($force = false) {
        if ($force === true || empty($this->tableColumns)) {
            if (isset(AetherORM::$columnCache[$this->objectName])) {
                // Use cached column information
                $this->tableColumns = AetherORM::$columnCache[$this->objectName];
            }
            else {
                // Load table columns
                AetherORM::$columnCache[$this->objectName] = $this->tableColumns =
                    $this->listFields();
            }
        }

        return $this;
    }

    /**
     * Tests if this object has a relationship to a different model.
     *
     * @param object $model related AetherORM model
     * @param boolean $any check for any relations to given model
     * @return boolean
     */
    public function has(AetherORM $model, $any = false) {
        // Determine plural or singular relation name
        $related = ($model->tableNamesPlural === true) ?
            $model->objectPlural : $model->objectName;

        if (($joinTable = array_search($related, $this->hasAndBelongsToMany)) === false)
            return false;

        if (is_int($joinTable)) {
            // No "through" table, load the default JOIN table
            $joinTable = $model->joinTable($this->tableName);
        }

        if (!isset($this->objectRelations[$related])) {
            // Load the object relationships
            $this->changedRelations[$related] =
                $this->objectRelations[$related] =
                $this->loadRelations($joinTable, $model);
        }

        if (!$model->emptyPrimaryKey()) {
            // Check if a specific object exists
            return in_array($model->primaryKeyValue,
                            $this->changedRelations[$related]);
        }
        elseif ($any) {
            // Check if any relations to given model exist
            return !empty($this->changedRelations[$related]);
        }
        else {
            return false;
        }
    }

    /**
     * Adds a new relationship to between this model and another.
     *
     * @param object $model related AetherORM model
     * @return boolean
     */
    public function add(AetherORM $model) {
        if ($this->has($model))
            return true;

        // Get the faked column name
        $column = $model->objectPlural;

        // Add the new relation to the update
        $this->changedRelations[$column][] = $model->primaryKeyValue;

        if (isset($this->related[$column])) {
            // Force a reload of the relationships
            unset($this->related[$column]);
        }

        return true;
    }

    /**
     * Adds a new relationship to between this model and another.
     *
     * @param object $model related AetherORM model
     * @return boolean
     */
    public function remove(AetherORM $model) {
        if (!$this->has($model))
            return false;

        // Get the faked column name
        $column = $model->objectPlural;

        if (($key = array_search($model->primaryKeyValue,
                                 $this->changedRelations[$column])) === false) {
            return false;
        }

        // Remove the relationship
        unset($this->changedRelations[$column][$key]);

        if (isset($this->related[$column])) {
            // Force a reload of the relationships
            unset($this->related[$column]);
        }

        return true;
    }

    /**
     * Count the number of records in the table.
     *
     * @return  integer
     */
    public function countAll() {
        // Return the total number of records in a table
        return $this->db->countRecords($this->tableName);
    }

    /**
     * Proxy method to Database list_fields.
     *
     * @param string $table table name or NULL to use this table
     * @return array
     */
    public function listFields($table = NULL) {
        if ($table === NULL)
                $table = $this->tableName;

        // Proxy to database
        return $this->db->listFields($table);
    }

    /**
     * Proxy method to Database field_data.
     *
     * @param string $table table name
     * @return array
     */
    public function fieldData($table) {
        // Proxy to database
        return $this->db->fieldData($table);
    }

    /**
     * Proxy method to Database field_data.
     *
     * @param string $sql SQL query to clear
     * @return AetherORM
     */
    public function clearCache($sql = NULL) {
        // Proxy to database
        $this->db->clearCache($sql);

        AetherORM::$columnCache = array();

        return $this;
    }

    /**
     * Returns the unique key for a specific value. This method is expected
     * to be overloaded in models if the model has other unique columns.
     *
     * @param mixed $id unique value
     * @return string
     */
    public function uniqueKey($id) {
        return $this->primaryKey;
    }

    /**
     * Determines the name of a foreign key for a specific table.
     *
     * @param string $table related table name
     * @param string $prefixTable prefix table name (used for JOINs)
     * @return string
     */
    public function foreignKey($table = NULL, $prefixTable = NULL) {
        if ($table === true) {
            if (is_string($prefixTable)) {
                // Use prefix table name and this table's PK
                return $prefixTable .'.'. $this->primaryKey;
            }
            else {
                // Return the name of this table's PK
                return $this->tableName.'.'.$this->primaryKey;
            }
        }

        if (is_string($prefixTable)) {
            // Add a period for prefixTable.column support
            $prefixTable .= '.';
        }

        if (isset($this->foreignKey[$table])) {
            // Use the defined foreign key name, no magic here!
            $foreignKey = $this->foreignKey[$table];
        }
        else {
            if (!is_string($table) ||
                !array_key_exists($table .'_'. $this->primaryKey, $this->object)) {
                // Use this table
                $table = $this->tableName;

                if (strpos($table, '.') !== false) {
                    // Hack around support for PostgreSQL schemas
                    list ($schema, $table) = explode('.', $table, 2);
                }

                if ($this->tableNamesPlural === true) {
                    // Make the key name singular
                    $table = Inflector::singular($table);
                }
            }

            $foreignKey = $table .'_'. $this->primaryKey;
        }

        return $prefixTable . $foreignKey;
    }

    /**
     * This uses alphabetical comparison to choose the name of the table.
     *
     * Example: The joining table of users and roles would be roles_users,
     * because "r" comes before "u". Joining products and categories would
     * result in categories_products, because "c" comes before "p".
     *
     * Example: zoo > zebra > robber > ocean > angel > aardvark
     *
     * @param string $table table name
     * @return string
     */
    public function joinTable($table) {
        if ($this->tableName > $table)
            $table = $table .'_'. $this->tableName;
        else
            $table = $this->tableName .'_'. $table;

        return $table;
    }

    /**
     * Returns an AetherORM model for the given object name;
     *
     * @param string $object object name
     * @return AetherORM
     */
    protected function relatedObject($object) {
        if (isset($this->hasOne[$object]))
            $object = AetherORM::factory($this->hasOne[$object]);
        elseif (isset($this->belongsTo[$object]))
            $object = AetherORM::factory($this->belongsTo[$object]);
        elseif (in_array($object, $this->hasOne) || in_array($object, $this->belongsTo))
            $object = AetherORM::factory($object);
        else
            return false;

        return $object;
    }

    /**
     * Loads an array of values into into the current object.
     *
     * @param array $values values to load
     * @return AetherORM
     */
    public function loadValues(array $values) {
        if (array_key_exists($this->primaryKey, $values)) {
            // Replace the object and reset the object status
            $this->object = $this->changed = $this->related = array();

            // Set the loaded and saved object status based on the primary key
            $this->loaded = $this->saved = ($values[$this->primaryKey] !== NULL);
        }

        // Related objects
        $related = array();

        foreach ($values as $column => $value) {
            if (strpos($column, ':') === false) {
                if (isset($this->tableColumns[$column])) {
                    // The type of the value can be determined, convert the value
                    $value = $this->loadType($column, $value);
                }

                $this->object[$column] = $value;
            }
            else {
                list($prefix, $column) = explode(':', $column, 2);
                $related[$prefix][$column] = $value;
            }
        }

        if (!empty($related)) {
            foreach ($related as $object => $values) {
                // Load the related objects with the values in the result
                $this->related[$object] = $this->relatedObject($object)
                    ->loadValues($values);
            }
        }

        return $this;
    }

    /**
     * Loads a value according to the types defined by the column metadata.
     *
     * @param string $column column name
     * @param mixed $value value to load
     * @return mixed
     */
    protected function loadType($column, $value) {
        $type = gettype($value);
        if ($type == 'object' || $type == 'array' ||
            !isset($this->tableColumns[$column])) {
            return $value;
        }

        // Load column data
        $column = $this->tableColumns[$column];

        if ($value === NULL && !empty($column['null']))
            return $value;

        if (!empty($column['binary']) && !empty($column['exact']) &&
            (int)$column['length'] === 1) {
            // Use boolean for BINARY(1) fields
            $column['type'] = 'boolean';
        }

        switch ($column['type']) {
        case 'int':
            if ($value === '' && !empty($column['null'])) {
                // Forms will only submit strings, so empty integer values must be null
                $value = NULL;
            }
            elseif ((float)$value > PHP_INT_MAX) {
                // This number cannot be represented by a PHP integer, so we convert it to a string
                $value = (string)$value;
            }
            else {
                $value = (int)$value;
            }
            break;
        case 'float':
            $value = (float)$value;
            break;
        case 'boolean':
            $value = (bool)$value;
            break;
        case 'string':
            $value = (string)$value;
            break;
        }

        return $value;
    }

    /**
     * Loads a database result, either as a new object for this model, or as
     * an iterator for multiple rows.
     *
     * @param boolean $array return an iterator or load a single row
     * @return AetherORM for single rows
     * @return AetherORMIterator for multiple rows
     */
    protected function loadResult($array = false) {
        if ($array === false) {
            // Only fetch 1 record
            $this->db->limit(1);
        }

        if (!isset($this->dbApplied['select'])) {
            // Select all columns by default
            $this->db->select($this->tableName . '.*');
        }

        if (!empty($this->loadWith)) {
            foreach ($this->loadWith as $alias => $object) {
                // Join each object into the results
                if (is_string($alias)) {
                    // Use alias
                    $this->with($alias);
                }
                else {
                    // Use object
                    $this->with($object);
                }
            }
        }

        if (!isset($this->dbApplied['orderby']) && !empty($this->sorting)) {
            $sorting = array();
            foreach ($this->sorting as $column => $direction) {
                if (strpos($column, '.') === false) {
                    // Keeps sorting working properly when using JOINs on
                    // tables with columns of the same name
                    $column = $this->tableName .'.'. $column;
                }
                
                $sorting[$column] = $direction;
            }

            // Apply the user-defined sorting
            $this->db->orderby($sorting);
        }

        // Load the result
        $result = $this->db->get($this->tableName);

        if ($array === true) {
            // Return an iterated result
            return new AetherORMIterator($this, $result);
        }

        if ($result->count() === 1) {
            // Load object values
            $this->loadValues($result->result(false)->current());
        }
        else {
            // Clear the object, nothing was found
            $this->clear();
        }

        return $this;
    }

    /**
     * Return an array of all the primary keys of the related table.
     *
     * @param string $table table name
     * @param object $model AetherORM model to find relations of
     * @return array
     */
    protected function loadRelations($table, AetherORM $model) {
        // Save the current query chain (otherwise the next call will clash)
        $this->db->push();
        
        $query = $this->db
            ->select($model->foreignKey(NULL).' AS id')
            ->from($table)
            ->where($this->foreignKey(NULL, $table), $this->object[$this->primaryKey])
            ->get()
            ->result(true);

        $this->db->pop();

        $relations = array();
        foreach ($query as $row)
            $relations[] = $row->id;

        return $relations;
    }

    /**
     * Returns whether or not primary key is empty
     *
     * @return bool
     */
    protected function emptyPrimaryKey() {
        return (empty($this->object[$this->primaryKey]) &&
                $this->object[$this->primaryKey] !== '0');
    }

}
