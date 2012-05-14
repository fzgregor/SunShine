<?php
require_once 'database/orm.php';

class Plant extends Model{
	static public $tableName = "plant";
	static public $fields = array(
		"name" => "VARCHAR(50)",
		"build_date" => "INTEGER",
		"captured_since" => "INTEGER",
		"login" => "VARCHAR(50)",
		"password" => "VARCHAR(50)",
		"power_peak" => "INTEGER",
		"panel_area" => "FLOAT"
	);
	
	static function getIdToNameArray($where="1"){
		$query = Plant::getDb()->query("SELECT id, name FROM plant WHERE $where;");
		foreach ($query as $entry){
			$result[$entry['id']] = $entry['name'];
		}
		return $result;
	}
	
	function countMeasurements(){
		return $this->getDb()->getCol("SELECT COUNT(id) FROM measurement WHERE plant = \"$this->id\";");
	}
	
	function getQualityOfServiceToday(){
		return $this->getQualityOfServiceBetween(strtotime("today"), time());
	}
	
	function getQualityOfServiceYesterday(){
		return $this->getQualityOfServiceBetween(strtotime("yesterday"), strtotime("today"));
	}
	
	function getQualityOfServiceThisWeek(){
		return $this->getQualityOfServiceBetween(thisWeek(), time());
	}
	
	function getQualityOfServiceLastWeek(){
		return $this->getQualityOfServiceBetween(lastWeek(), thisWeek());
	}
	
	function getQualityOfServiceThisMonth(){
		return $this->getQualityOfServiceBetween(thisMonth(), time());
	}
	
	function getQualityOfServiceLastMonth(){
		return $this->getQualityOfServiceBetween(lastMonth(), thisMonth());
	}
	
	function getQualityOfServiceThisYear(){
		return $this->getQualityOfServiceBetween(thisYear(), time());
	}
	
	function getQualityOfServiceLastYear(){
		return $this->getQualityOfServiceBetween(lastYear(), thisYear());
	}
	
	function getQualityOfServiceOverall(){
		return $this->getQualityOfServiceBetween($this->captured_since, time());
	}
	
	function getQualityOfServiceBetween($start, $end){
		$target = $this->getDb()->getCol("
			SELECT 
                COUNT(day.id) 
			FROM 
                day, quarterhoursoftheday 
			WHERE
                day.day + quarterhoursoftheday.time >= $start AND 
				day.day + quarterhoursoftheday.time < $end;");
		$actual = $this->getDb()->getCol("
			SELECT
                COUNT(day.id) 
			FROM
                measurement 
			LEFT JOIN
                day ON (day.id = measurement.day)
			WHERE
                plant = $this->id AND 
				day.day + measurement.time >= $start AND
				day.day + measurement.time < $end;");
        return round($actual/$target*100, 2);
	}
	
	function getPowerToday(){
		return $this->getPowerBetween(strtotime("today"), time());
	}
	
	function getPowerYesterday(){
		return $this->getPowerBetween(strtotime("yesterday"), strtotime("today"));
	}
	
	function getPowerThisWeek(){
		return $this->getPowerBetween(thisWeek(), time());
	}
	
	function getPowerLastWeek(){
		return $this->getPowerBetween(lastWeek(), thisWeek());
	}
	
	function getPowerThisMonth(){
		return $this->getPowerBetween(thisMonth(), time());
	}
	
	function getPowerLastMonth(){
		return $this->getPowerBetween(lastMonth(), thisMonth());
	}
	
	function getPowerThisYear(){
		return $this->getPowerBetween(thisYear(), time());
	}
	
	function getPowerLastYear(){
		return $this->getPowerBetween(lastYear(), thisYear());
	}
	
	function getPowerOverall(){
		return $this->getPowerBetween(0, time());
	}

    // has to be updated on db change
    function getPowerBetween($start, $end){
        return $this->getDb()->getCol("
            SELECT
                IFNULL(ROUND(SUM(acp)/4, 2), 0)
            FROM
                measurement m
            LEFT JOIN
                day d ON (d.id = m.day)
            WHERE
                d.day + m.time >= $start AND
                d.day + m.time < $end AND
                m.plant = $this->id;");
    }
	
	function getMeasurementDataToday(){
		return $this->getMeasurementDataBetween(strtotime("today"), strtotime("now"));
	}
	
	function getMeasurementDataBetween($start, $end){
		return $this->getDb()->query("
            SELECT 
                d.day + q.time as time, round(dcu,2) as dcu, round(dcp,2) as dcp, round(acp,2) as acp, round(temperature,2) as temperature, round(irradiation,2) as irradiation
            FROM
                day d
            LEFT JOIN
                quarterhoursoftheday q ON 1
            LEFT OUTER JOIN
                measurement m ON (d.id = m.day AND q.time = m.time AND m.plant = $this->id)
            WHERE
                d.day + q.time >= $start AND
                d.day + q.time <  $end
            ORDER BY
                d.day + q.time ASC;");
	}
	
    // bullshit method forwardings. needed for PHP 5.2
	static function isCreated(){
        return parent::isCreated();
    }
	static function create(){
        return parent::create();
    }
	static function createIfNecessary(){
        return parent::createIfNecessary();
    }
	static function getOne($where="1"){
        return parent::getOne($where);
    }
	static function getAll($where="1"){
        return parent::getAll($where);
    }
	static function count($where="1"){
        return parent::count($where);
    }
}

class Session extends Model{
	static public $tableName = "session";
	static public $fields = array(
		"plant" => "INTEGER",
		"session_id" => "VARCHAR(50) UNIQUE",
		"started_at" => "INTEGER",
		"stopped_at" => "INTEGER",
		"upload_count" => "INTEGER",
	);
	static public $tableConstrain = "FOREIGN KEY (plant) REFERENCES plant(id)";
	
	static function openNewSessionFor($plant){
		if (!is_int($plant)){
			$plant = $plant->id;
		}
		
		// close all open sessions of this plant
		foreach (Session::getAll("plant = \"$plant\" AND session_id != NULL") as $session){
			$session->close();
		}
		
		// create new session id
		// seeding the random genarator
		mt_srand(time());
		do {
			$newSessionTry = base_convert(mt_rand(), 10, 36);
			//$newSessionTry = mt_rand();
			$exists = Session::getSessionWithId($newSessionTry);
		} while ($exists);
		
		// create session & fill with data
		$newSession = new Session();
		$newSession->plant = $plant;
		$newSession->session_id = $newSessionTry;
		$newSession->started_at = time();
		$newSession->upload_count = 0;
		// save it
		$newSession->save();
		
		return $newSession;
	}
	
	static function getSessionWithId($session_id){
		$id = Model::getDb()->getCol("SELECT id FROM session WHERE session.session_id = \"$session_id\";");
		if (!$id){
			return false;
		}
		return new Session($id);
	}
	
	function close(){
		unset($this->session_id);
		$this->stopped_at = time();
		$this->save();
	}
	
    // bullshit method forwardings. needed for PHP 5.2
	static function isCreated(){
        return parent::isCreated();
    }
	static function create(){
        return parent::create();
    }
	static function createIfNecessary(){
        return parent::createIfNecessary();
    }
	static function getOne($where="1"){
        return parent::getOne($where);
    }
	static function getAll($where="1"){
        return parent::getAll($where);
    }
	static function count($where="1"){
        return parent::count($where);
    }
}

class Day extends Model{
	static public $tableName = "day";
	static public $fields = array(
		"day" => "INTEGER UNIQUE",// midnight of the day in seconds from the epoch
	);
	
	static function getDayFor($time){
		$time = floor($time/86400)*86400;
		$id = Day::getDb()->getCol("SELECT id FROM day WHERE day.day = \"$time\"");
		if (!$id){
			$newDay = new Day();
			$newDay->day = $time;
			$newDay->save();
			return $newDay;
		} else {
			return new Day($id);
		}
	}
	
	static function createDays($until){
		//TODO should be optimized
		$cur = Day::getDb()->getCol("SELECT max(day) FROM day;");
		if (!$cur){
			$cur = strtotime("01.01.2006");
		}
		while ($cur < $until){
			Day::getDayFor($cur);
			$cur += 86400;
		}
	}
	
    // bullshit method forwardings. needed for PHP 5.2
	static function isCreated(){
        return parent::isCreated();
    }
	static function create(){
        return parent::create();
    }
	static function createIfNecessary(){
        return parent::createIfNecessary();
    }
	static function getOne($where="1"){
        return parent::getOne($where);
    }
	static function getAll($where="1"){
        return parent::getAll($where);
    }
	static function count($where="1"){
        return parent::count($where);
    }
}

// this model is required for dataexport without timegaps even when measurement data has gaps
class QuarterHoursOfTheDay extends Model{
	static public $tableName = "quarterhoursoftheday";
	static public $fields = array(
		"time" => "INTEGER UNIQUE",// time from 0 (midnight) to 85500 in 900 intervalls
	);
	
	static function createIfNecessary(){
        if (!self::isCreated()){
            self::create();
		    for ($i=0; $i < 4*24; $i++){
			    $n = new QuarterHoursOfTheDay();
			    $n->time = $i * 900;
			    $n->save();
		    }
        }
            
    }
	
    // bullshit method forwardings. needed for PHP 5.2
	static function isCreated(){
        return parent::isCreated();
    }
	static function create(){
		parent::create();
	}
	static function getOne($where="1"){
        return parent::getOne($where);
    }
	static function getAll($where="1"){
        return parent::getAll($where);
    }
	static function count($where="1"){
        return parent::count($where);
    }
}

class Measurement extends Model{
	static public $tableName = "measurement";
	static public $fields = array(
		"plant" => "INTEGER", // id of plant
		"day" => "INTEGER", // id of day
		"time" => "INTEGER", // time from 0 (midnight) to 85500 in 900 intervalls
		"acp" => "FLOAT",
		"dcp" => "FLOAT",
		"dcu" => "FLOAT",
		"temperature" => "FLOAT",
		"irradiation" => "FLOAT",
		"contains_minutes" => "VARCHAR(35)", // see addNewMeasurement()
	);
	static public $tableConstrain = "FOREIGN KEY (plant) REFERENCES plant(id), FOREIGN KEY (day) REFERENCES day(day)";
	static public $backupRaw = true; // whether entered measurements should be backuped as MeasurementRaw entry
	public $overwrite = false; // if direct setting of measurement data ($m->acp = 42) should be permitted
	
	// has to be overwritten for direct access lock
	function __set($name, $value){
		if (!$this->overwrite){
			throw new ModelException(get_class()." is a special Model you shouldn't set data of it! If you know what you're doing set Measurement::\$overwrite to true");
		}
		parent::__set($name, $value);
	}
	// has to be overwritte so loading is possible
	function _load($raw_array){
		$this->overwrite = true;
		parent::_load($raw_array);
		$this->overwrite = false;
		
	}
	
	// automatically fill captured_since field of plant
	function save(){
		$plant = new Plant($this->plant);
		if (!$plant->captured_since){
			$day = new Day($this->day);
			$plant->captured_since = $this->time + $day->day;
			$plant->save();
		}
		parent::save();
	}
	
	static function getMeasurementFor($plant, $time){
		if (!is_int($plant)){
			$plant = $plant->id;
		}
		$day = Day::getDayFor($time);
		$time = floor(($time - $day->day)/900)*900;
		$id = Measurement::getDb()->getCol("SELECT id FROM measurement WHERE measurement.plant = \"$plant\" AND measurement.day = \"$day->id\" AND measurement.time = \"$time\";");
		if (!$id){
			$newM = new Measurement();
			$newM->modelData['plant'] = $plant;
			$newM->modelData['day'] = $day->id;
			$newM->modelData['time'] = $time;
			return $newM;
		} else {
			return new Measurement($id);
		}
	}
	
	function addNewMeasurement($new_measure){
		$needed_fields = array("time", "acp", "dcp", "dcu", "temperature", "irradiation");
		foreach ($needed_fields as $cur){
			if (!in_array($cur,array_keys($new_measure))){
				throw new ModelException("If you want to add a new Measurement it has to contain all of time, acp, dcp, dcu, temperature and irradiation!", 1);
			}
		}
		array_shift($needed_fields);
		$time = $new_measure['time'];
		$newDay = Day::getDayFor($time);
		$time = $time - $newDay->day - $this->time;
		if ($time < 0 OR $time >= 900 OR $newDay->id != $this->day){
			throw new ModelException("You wanted to add a measure to a Measurement it does not fit to (other time)!", 2);
		}
		
		$minute = floor($time/60);
		//TODO: is this now really a sting? otherwise treated as ordinal value
		if (strpos($this->contains_minutes, (string) $minute) !== false){
			throw new ModelException("This measure is already in the measurement!", 3);
		}
		
		if ($this->contains_minutes === NULL){
			$old_ratio = 0;
		} else {
			$chars = count_chars($this->contains_minutes, 0);
			$old_ratio = $chars[44] + 1;
		}
		$new_ratio = $old_ratio + 1;
		
		// add minute
		if ($old_ratio == 0){
			$this->modelData['contains_minutes'] = $minute;
		} else {
			$this->modelData['contains_minutes'] = $this->modelData['contains_minutes']. ",$minute";
		}
		
		foreach($needed_fields as $cur){
			if (array_key_exists($cur, $this->modelData)){
				$this->modelData[$cur] = ($this->modelData[$cur]*$old_ratio + $new_measure[$cur])/$new_ratio;
			} else {
				$this->modelData[$cur] = $new_measure[$cur];
			}
		}
		$this->needsUpdate = true;
		
		if (Measurement::$backupRaw){
			$bak = new MeasurementRaw();
			$bak->plant = $this->plant;
			$bak->day = $this->day;
			$bak->time = $new_measure['time'];
			$bak->acp = $new_measure['acp'];
			$bak->dcp = $new_measure['dcp'];
			$bak->dcu = $new_measure['dcu'];
			$bak->temperature = $new_measure['temperature'];
			$bak->irradiation = $new_measure['irradiation'];
			$bak->save();
		}
	}
	
    // bullshit method forwardings. needed for PHP 5.2
	static function isCreated(){
        return parent::isCreated();
    }
	static function create(){
        return parent::create();
    }
	static function createIfNecessary(){
        return parent::createIfNecessary();
    }
	static function getOne($where="1"){
        return parent::getOne($where);
    }
	static function getAll($where="1"){
        return parent::getAll($where);
    }
	static function count($where="1"){
        return parent::count($where);
    }
}

class MeasurementRaw extends Model{
	static public $tableName = "measurement_raw";
	static public $fields = array(
		"plant" => "INTEGER",
		"day" => "INTEGER",
		"time" => "INTEGER",
		"acp" => "INTEGER",
		"dcp" => "INTEGER",
		"dcu" => "INTEGER",
		"temperature" => "INTEGER",
		"irradiation" => "INTEGER",
	);
	static public $tableConstrain = "FOREIGN KEY (plant) REFERENCES plant(id), FOREIGN KEY (day) REFERENCES day(day)";
	
    // bullshit method forwardings. needed for PHP 5.2
	static function isCreated(){
        return parent::isCreated();
    }
	static function create(){
        return parent::create();
    }
	static function createIfNecessary(){
        return parent::createIfNecessary();
    }
	static function getOne($where="1"){
        return parent::getOne($where);
    }
	static function getAll($where="1"){
        return parent::getAll($where);
    }
	static function count($where="1"){
        return parent::count($where);
    }
}

?>
