<?php

class EntityModel extends AetherORM {
    protected $db = 'pg2_backend';
    protected $tableName = 'entity';

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