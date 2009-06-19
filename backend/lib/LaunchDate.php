<?php // 
require_once('/home/lib/libDefines.lib.php');
require_once(LIB_PATH . 'ActiveRecord.php');
/**
 * 
 * LaunchDate w/helper
 * 
 * Created: 2009-06-15
 * @author Simen Graaten
 * @package prisguide.backend.lib
 */

class LaunchDate extends ActiveRecord {
    protected $id;
    protected $entityId;
    protected $startDate;
    protected $endDate;

    public $tableInfo = array(
        'database' => 'pg2_backend',
        'table' => 'launch_date',
        'keys' => array(
            'id' => 'id'
        ),
        'indexes' => array(
            'id' => 'id',
            'entity_id' => 'entityId',
            'start_date' => 'startDate',
            'end_date' => 'endDate'
        ),
        'fields' => array(
            'id' => 'id',
            'entity_id' => 'entityId',
            'start_date' => 'startDate',
            'end_date' => 'endDate'
        ),
        'relations' => array(
        )
    );

    /**
     * Sets startDate + endDate based on year + month/Qn/Hn
     *
     * @param $year int
     * @param $period string
     */
    public function setInterval($year, $period = false) {
        if ($period == false) {
            $start = $year . "-01-01";
            $end = ($year+1) . "-01-01 -1days";
        }
        else {
            if ($period == "Q1" || $period == "H1")
                $start = sprintf("%04d-01-01", $year);
            else if ($period == "Q2")
                $start = sprintf("%04d-04-01", $year);
            else if ($period == "Q3" || $period == "H2")
                $start = sprintf("%04d-07-01", $year);
            else if ($period == "Q4")
                $start = sprintf("%04d-10-01", $year);
            else
                $start = sprintf("%04d-%02d-01", $year, $period);

            if (substr($period, 0, 1) == "Q")
                $end = $start . " +3months-1days";
            else if (substr($period, 0, 1) == "H")
                $end = $start . " +6months-1days";
            else
                $end = $start . " +1months-1days";
        }

        $this->set('startDate', date("Y-m-d", strtotime($start)));
        $this->set('endDate', date("Y-m-d", strtotime($end)));
    }

    /**
     * Gets a interval based on stored startDate + endDate
     */
    public function getInterval() {
        if ($this->get('startDate') === null || $this->get('endDate') === null) {
            return false;
        }
        $year = date('Y', strtotime($this->get('startDate')));
        
        $start = $this->get('startDate');
        $end = $this->get('endDate');

        $startMD = substr($start, 5, 5);
        $endMD = substr($end, 5, 5);

        $period = false;

        // Assume month if startdate is 1. and enddate is 1 month after
        if (substr($startMD, -2) == "01") {
            if (strtotime($start) == strtotime(date("Y-m-d", 
                        strtotime($end . " +1days")) . " -1months")) {
                $period = date("m", strtotime($start));
            }
        }
        
        if ($startMD == "01-01") {
            if ($endMD == "03-31")
                $period = "Q1";
            else if ($endMD == "06-30")
                $period = "H1";
        }
        else if ($startMD == "04-01" && $endMD == "06-30") {
            $period = "Q2";
        }
        else if ($startMD == "07-01") {
            if ($endMD == "09-30") 
                $period = "Q3";
            else if ($endMD == "12-31")
                $period = "H2";
        }
        else if ($startMD == "10-01" && $endMD == "12-31") {
            $period = "Q4";
        }

        return array('year' => $year, 'period' => $period);
    }
}
?>
