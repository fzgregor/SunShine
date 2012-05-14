<?php

define("DAY", 86400);
define("MONTH", 2635200);
define("YEAR", 961848000);

class MeasurementData{
    public $time, $dcu, $dcp, $acp, $temperature, $irradiation;
    private $results;
    private $plant;
    private $space;

    function MeasurementData($plant){
        if (is_int($plant)){
            $this->plant = new Plant($plant);
        } else if (is_a($plant, "Plant")){
            $this->plant = $plant;
        } else {
            throw new Exception("Argument should be plant object or integer.");
        }

        $this->reset();
    }

    function getPlant(){
        return $this->plant;
    }

    private function reset(){
        $this->time = false;
        $this->dcu = false;
        $this->dcp = false;
        $this->acp = false;
        $this->temperature = false;
        $this->irradiation = false;
    }

    function setNext(){
        if (isset($this->results) && is_array($this->results) && count($this->results) > 0){
            $next = array_shift($this->results);
            $this->time = $next["time"];
            $this->dcu = $next["dcu"];
            $this->dcp = is_null($this->dcu) ? NULL : $next["dcp"];
            $this->acp = is_null($this->dcu) ? NULL : $next["acp"];
            $this->temperature = $next["temperature"];
            $this->irradiation = $next["irradiation"];
            return true;
        } else {
            $this->reset();
            return false;
        }
    }

    function __get($name){
        $d = get_object_vars($this);
        if (in_array($name, array("time", "dcu", "dcp", "acp", "temperature", "irradiation"))){
            if ($d[$name] == false){
                throw new Exception("No data!");
            } else {
                return $d[$name];
            }
        } else if ($name == "dci"){
            return $this->dcp / $this->dcu;
        } else {
            return $d[$name];
        }
    }

    function setSpace($space){
        $this->space = $space;
    }

    function setTime($time){
        $this->time = $time;
    }

    private function start(){
        switch ($this->space){
            case DAY:
                $date_str = "d.m.Y";
                break;
            
            case MONTH:
                $date_str = "01.m.Y";
                break;
            
            case YEAR:
                $date_str = "01.01.Y";
                break;
            
        }

        return strtotime(Date($date_str, $this->time));
    }

    private function end(){
        switch ($this->space){
            case DAY:
                $date_str = "+1 day";
                break;
            
            case MONTH:
                $date_str = "+1 month";
                break;
            
            case YEAR:
                $date_str = "+1 year";
                break;
            
        }

        return strtotime($date_str, $this->start());
    }

    private function groupByClause(){
        $db = $this->plant->getDb();
        switch ($this->space){
            case DAY:
                return "
            GROUP BY
                ".$db->func_extract_hour("d.day + q.time");
                break;
            
            case MONTH:
                return "
            GROUP BY
                ".$db->func_extract_day("d.day + q.time");
                break;
            
            case YEAR:
                return "
            GROUP BY
                ".$db->func_extract_month("d.day + q.time");
                break;
            
        }

        
    }

    function query(){
        $sql = "
            SELECT 
                min(d.day + q.time) as time,
                round(avg(dcu),2) as dcu,
                round(sum(dcp)/4,2) as dcp,
                round(sum(acp)/4,2) as acp,
                round(avg(temperature),2) as temperature,
                round(avg(irradiation),2) as irradiation
            FROM
                day d
            LEFT JOIN
                quarterhoursoftheday q ON 1
            LEFT OUTER JOIN
                measurement m ON (d.id = m.day AND q.time = m.time AND m.plant = ".$this->plant->id.")
            WHERE
                d.day + q.time >= ".$this->start()." AND
                d.day + q.time <  ".$this->end();
        
        $sql .= $this->groupByClause();

        $sql .= "
            ORDER BY
                d.day + q.time ASC;";

        $this->results = $this->plant->getDb()->query($sql);
    }
}
