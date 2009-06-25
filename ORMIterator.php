<?php

class AetherORMIterator implements Iterator, ArrayAccess, Countable {

    // Class attributes
    protected $className;
    protected $primaryKey;
    protected $primaryVal;

    // Database result object
    protected $result;

    public function __construct(AetherORM $model, AetherDatabaseResult $result) {
        // Class attributes
        $this->className  = get_class($model);
        $this->primaryKey = $model->primaryKey;
        $this->primaryVal = $model->primaryVal;

        // AetherDatabase result
        $this->result = $result->result(true);
    }

    /**
     * Returns an array of the results as AetherORM objects.
     *
     * @return  array
     */
    public function asArray() {
        $array = array();

        if ($results = $this->result->resultArray()) {
            // Import class name
            $class = $this->className;

            foreach ($results as $obj)
                $array[] = new $class($obj);
        }

        return $array;
    }

    /**
     * Return an array of all of the primary keys for this object.
     *
     * @return  array
     */
    public function primaryKeyArray() {
        $ids = array();
        foreach ($this->result as $row)
            $ids[] = $row->{$this->primaryKey};
        
        return $ids;
    }

    /**
     * Create a key/value array from the results.
     *
     * @param string $key key column
     * @param string $val value column
     * @return array
     */
    public function selectList($key = NULL, $val = NULL) {
        if ($key === NULL) {
            // Use the default key
            $key = $this->primaryKey;
        }

        if ($val === NULL) {
            // Use the default value
            $val = $this->primaryVal;
        }

        $array = array();
        foreach ($this->result->resultArray() as $row)
            $array[$row->$key] = $row->$val;
        
        return $array;
    }

    /**
     * Return a range of offsets.
     *
     * @param   integer  start
     * @param   integer  end
     * @return  array
     */
    public function range($start, $end) {
        // Array of objects
        $array = array();

        if ($this->result->offsetExists($start)) {
            // Import the class name
            $class = $this->className;

            // Set the end offset
            $end = $this->result->offsetExists($end) ? $end : $this->count();

            for ($i = $start; $i < $end; $i++) {
                // Insert each object in the range
                $array[] = new $class($this->result->offsetGet($i));
            }
        }

        return $array;
    }

    /**
     * Countable: count
     */
    public function count() {
        return $this->result->count();
    }

    /**
     * Iterator: current
     */
    public function current() {
        if ($row = $this->result->current()) {
            // Import class name
            $class = $this->className;

            $row = new $class($row);
        }

        return $row;
    }

    /**
     * Iterator: key
     */
    public function key() {
        return $this->result->key();
    }

    /**
     * Iterator: next
     */
    public function next() {
        return $this->result->next();
    }

    /**
     * Iterator: rewind
     */
    public function rewind() {
        $this->result->rewind();
    }

    /**
     * Iterator: valid
     */
    public function valid() {
        return $this->result->valid();
    }

    /**
     * ArrayAccess: offsetExists
     */
    public function offsetExists($offset) {
        return $this->result->offsetExists($offset);
    }

    /**
     * ArrayAccess: offsetGet
     */
    public function offsetGet($offset) {
        if ($this->result->offsetExists($offset)) {
            // Import class name
            $class = $this->className;

            return new $class($this->result->offsetGet($offset));
        }
    }

    /**
     * ArrayAccess: offsetSet
     *
     * @throws  DatabaseException
     */
    public function offsetSet($offset, $value) {
        throw new DatabaseException('database.result_read_only');
    }

    /**
     * ArrayAccess: offsetUnset
     *
     * @throws  DatabaseException
     */
    public function offsetUnset($offset) {
        throw new DatabaseException('database.result_read_only');
    }

}