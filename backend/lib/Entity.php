<?php
require_once('/home/lib/libDefines.lib.php');
require_once(LIB_PATH . 'ActiveRecord.php');
require_once(PG_PATH . 'lib/DetailValue.php');

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
        $this->setExportFields(array('details'),false);
        return parent::toArray();
    }
    
    /**
     * Filter out all non-deleted entitys from an array
     *
     * @return array
     * @param array $data
     */
    static public function removeDeletedArrayMembers($array) {
        $newArray = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $newArray[$key] = array('records'=>array());
                foreach ($val['records'] as $k => $r) {
                    if ($r['deletedAt'] == '')
                        $newArray[$key]['records'][$k] = $r;
                }
            }
            else {
                $newArray[$key] = $val;
            }
        }
        return $newArray;
    }
}
