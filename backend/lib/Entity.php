<?php
require_once('/home/lib/libDefines.lib.php');
require_once(LIB_PATH . 'ActiveRecord.php');
require_once(PG_PATH . 'backend/lib/DetailValue.php');

abstract class Entity extends ActiveRecord {
    protected $id;
    protected $createdAt;
    protected $replacedByEntityId;
    protected $publishedAt;
    protected $modifiedAt;
    protected $deletedAt;
    protected $title;

    // Tmp turned of caching completely
    protected $neverEverCache = true;

    /**
     * Persist record to database
     *
     * @access public
     * @return bool
     */
    public function save($idFromTable = 'entity') {
        parent::save($idFromTable);
    }
    
    /**
     * Override default toArray() to include Details
     *
     * @access public
     * @return string
     * @param string $srcCharset
     */
    public function toArray($srcCharset = 'ISO-8859-1') {
        $data = parent::toArray();
        // mix in details
        $data['details'] = array();
        foreach ($this->get('details')->getAll() as $d) {
            $data['details'][$d->get('id')] = $d->toArray();
        }
        return $data;
    }
}
