<?php
require_once("page.php");

class ADException extends Exception{};

class AnalysisData{
    public $series = array();
    public $space;
    private $plants;
    private $times;
    private $series_quiered;
    private $last_type;
    private $last_plant;
    public $last_start;
    public $subtitle;
    public $interval;

    // the scripts need the following http request variables
    // plant_ids : array of plant ids
    // space : one of day, month, year
    // times : array of unix timestamp
    // series_#n : array containing some of
    static public $chooseable_series = array("dcu", "dcp", "dci", "acp", "temperature", "irradiation");
    // links dataset number to plant id

    static private $type_to_unit = array(
                "dcp" => "W",
                "dcu" => "V",
                "dci" => "A",
                "acp" => "W",
                "temperature" => "Cº",
                "irradiation" => "W/m²",
        );

    static private $type_to_chart_type = array(
                "dcp" => "column",
                "dcu" => "line",
                "dci" => "line",
                "acp" => "column",
                "temperature" => "line",
                "irradiation" => "line",
        );

    static private $type_to_name = array(
                "dcp" => "Gleichspannungsleistung",
                "dcu" => "Gleichspannung",
                "dci" => "Gleichspannungsstrom",
                "acp" => "Wechselspannungsleistung",
                "temperature" => "Temperatur",
                "irradiation" => "Einstrahlung",
        );

    public function AnalysisData($space, $plants, $times, $series_queried){
        $this->space = $space;
        $this->plants = $plants;
        $this->times = $times;
        $this->series_queried = $series_queried;

        $this->checkSpace();
        $this->checkPlants();
        $this->checkTimes();
        $this->checkQueriedSeries();

        if (!count($this->plants) == count($this->times)){
            throw new ADException("");
        }

        switch ($this->space){
            case DAY:
                $this->interval = 60*60;
                break;
            case MONTH:
                $this->interval = 60*60*24;
                break;
            case YEAR:
                $this->interval = 60*60*24*30.5;
                break;
        }

        foreach($this->series_queried as $series_id => $queries){
            $plant = $plants[$series_id];
            $time = $times[$series_id];

            $md = new MeasurementData($plant);
            $md->setTime($time);
            $md->setSpace($space);
            $md->query();

            $dcp = array();
            $acp = array();
            $dcu = array();
            $dci = array();
            $temperature = array();
            $irradiation = array();
            $start = false;
            while ($md->setNext()){
                ($start === false) ? $start = $md->time : true;
	            $dcp[] = $md->dcp;
	            $acp[] = $md->acp;
	            $dcu[] = $md->dcu;
                $dci[] = isset($md->dcu) ? $md->dcu > 0 ? $md->dcp / $md->dcu : 0 : NULL;
	            $temperature[] = $md->temperature;
	            $irradiation[] = $md->irradiation;
            }

            $this->last_start = $start;
            $this->last_plant = $plant;

            in_array("dcp", $queries) ? $this->addSeries("dcp", $start, $dcp, $plant) : false;
            in_array("acp", $queries) ? $this->addSeries("acp", $start, $acp, $plant) : false;
            in_array("dcu", $queries) ? $this->addSeries("dcu", $start, $dcu, $plant) : false;
            in_array("dci", $queries) ? $this->addSeries("dci", $start, $dci, $plant) : false;
            in_array("temperature", $queries) ? $this->addSeries("temperature", $start, $temperature, $plant) : false;
            in_array("irradiation", $queries) ? $this->addSeries("irradiation", $start, $irradiation, $plant) : false;
        }
        
        $oneTime = $this->oneTime();
        $onePlant = $this->onePlant();
        $oneType = $this->oneType();
        foreach($this->series as $key=>$series){
            $this->series[$key]["name"] = $this->rowName($series["plant"], $series["type"], $series["start"], $onePlant, $oneTime, $oneType);
        }

        $this->subtitle = $this->subtitle($onePlant, $oneTime, $oneType);
    }

    private function checkQueriedSeries(){
        if (!is_array($this->series_queried)){
            throw new ADException("");
        }
        foreach($this->series_queried as $series){
            if (!is_array($series)){
                throw new ADException("");
            }
            foreach($series as $s){
                if (!in_array($s, AnalysisData::$chooseable_series)){
                    throw new ADException("");
                }
            }
        }
    }

    private function checkSpace(){
        if (!in_array($this->space, array(DAY, MONTH, YEAR))){
            throw new ADException("");
        }
    }

    private function checkPlants(){
        if (!is_array($this->plants)){
            throw new ADException("");
        }
        foreach($this->plants as $plant){
            if (!is_a($plant, "Plant")){
                throw new ADException("");
            }
        }
    }

    private function checkTimes(){
        if (!is_array($this->times)){
            throw new ADException("");
        }
        foreach($this->times as $time){
            if(!($time > 0 && $time !== false)){
                throw new ADException("");
            }
        }
    }

    private function addSeries($type, $start, $series, $plant){
        $this->series[] = array(
            "type"=>$type,
            "start"=>$start, 
            "name"=>NULL,
            "unit"=>AnalysisData::$type_to_unit[$type],
            "chart_type"=>AnalysisData::$type_to_chart_type[$type],
            "series"=>$series,
            "plant"=>$plant,
        );
        $this->last_type = $type;
    }

    function oneTime(){
        $time = false;
        foreach($this->series as $series){
            if ($time !== false && $series["start"] != $time){
                 return false;
            } else {
                $time = $series["start"];
            }
        }
        return true;
    }

    private function onePlant(){
        $plant_id = false;
        foreach($this->series as $series){
            if ($plant_id !== false && $series["plant"]->id != $plant_id){
                return false;
            } else {
                $plant_id = $series["plant"]->id;
            }
        }
        return true;
    }

    private function oneType(){
        if (count($this->series) <= 1){
            return false;
        }
        $type = false;
        foreach($this->series as $series){
            if ($type !== false && $series["type"] != $type){
                return false;
            } else {
                $type = $series["type"];
            }
        }
        return true;
    }

    private function rowName($plant, $type, $start, $onePlant, $oneTime, $oneType){
        $name = array();
        if (!$onePlant){
            $name[] = $plant->name;
        }
        if (!$oneTime){
            switch($this->space){
                case DAY:
                    $name[] = date("d.m.Y", $start);
                    break;
                case MONTH:
                    $name[] = date("M Y", $start);
                    break;
                case YEAR:
                    $name[] = date("Y", $start);
                    break;
            }
        }
        if (!$oneType){
            $name[] = AnalysisData::$type_to_name[$type];
        }

        return implode(" ", $name);
    }

    private function subtitle($onePlant, $oneTime, $oneType){
        $name = array();
        if ($onePlant){
            $name[] = $this->last_plant->name;
        }
        if ($oneTime){
            switch($this->space){
                case DAY:
                    $name[] = date("d.m.Y", $this->last_start);
                    break;
                case MONTH:
                    $name[] = date("M Y", $this->last_start);
                    break;
                case YEAR:
                    $name[] = date("Y", $this->last_start);
                    break;
            }
        }
        if ($oneType){
            $name[] = AnalysisData::$type_to_name[$this->last_type];
        }
        return implode(" ", $name);
    }
}






function input_error($var=false, $msg=false){
    die("wrong user input".$var.$msg);
}

function plants(){
    if (!(array_key_exists("plant_ids", $_REQUEST) && is_array($_REQUEST["plant_ids"]))){
        input_error("plant_ids", "Doesn't exist or isn't array.");
    }

    $plants = array();
    foreach ($_REQUEST["plant_ids"] as $id){
        if (!is_numeric($id)){
            input_error("plant_ids", "At least one given id wasn't numeric.");
        }

        try{
            $plants[] = new Plant($id);
        }
        catch (Exception $e){
            input_error("plant_ids", "At least one id isn't existing.");
        }
    }

    return $plants;
}

function space(){
    if (array_key_exists("space", $_REQUEST)){
        switch ($_REQUEST["space"]){
            case "day":
                return DAY;
            case "month":
                return MONTH;
            case "year":
                return YEAR;
            default:
                input_error("space", "Not a valid space type.");
        }
    } else {
        input_error("space", "Not given.");
    }
}

function times(){
    if (!(array_key_exists("times", $_REQUEST) && is_array($_REQUEST["times"]))){
        input_error("times", "Doesn't exist or isn't array.");
    }
    $last_time = false;
    $times = array();
    foreach($_REQUEST["times"] as $time){
        $cur_time = strtotime($time);
        if($cur_time > 0 && $cur_time !== false){
            // time entry could be interpreted as correct time info
            $times[] = $cur_time;
            $last_time = $cur_time;
            // go to next entry
            continue;
        } else {
            // there is some problem with entry $i
            if ($last_time !== false){
                // but there were already a correct time info
                // take that
                $times[] = $last_time;
            } else {
                // thr$start, ow error
                input_error("times", "First time var is incorrect.");
            }
        }
    }

    return $times;
}

function series($plants=false){
    if($plants === false){
        $plants = plants();
    }
    $nr = count(array_keys($plants));
    $series = array();

    for($dataset_id = 0; $dataset_id < $nr; $dataset_id++){
        if (array_key_exists("series_".$dataset_id, $_REQUEST) && is_array($_REQUEST["series_".$dataset_id])){
            foreach($_REQUEST["series_".$dataset_id] as $query){
                if (in_array($query, AnalysisData::$chooseable_series)){
                    if (!array_key_exists($dataset_id, $series)){
                        $series[$dataset_id] = array();
                    }
                    $series[$dataset_id][] = $query;
                }
            }
        }
    }

    return $series;
}
