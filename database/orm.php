<?php

class DBException extends Exception{};

class DatabaseInterface{
	static protected $affectedRows;
	public $debug = false;
	static protected $lastId;
	protected $pk;
	
	function getPrimaryKeyType(){
//		$class_vars = get_class_vars(get_called_class());
//		return $class_vars['pk'];
		return $this->pk;
	}
	
	function fetchOne($table, $where="1"){
		$sql = "SELECT * FROM $table WHERE $where LIMIT 1;";
		$res_array = $this->query($sql);
		return $res_array[0];
	}
	
	function fetchAll($table, $where="1"){
		$sql = "SELECT * FROM $table WHERE $where;";
		return $this->query($sql);
	}
	
	function getCol($sql){
		//TODO: check whether sql contains LIMIT 1, if not append
		$res_array = $this->query($sql);
		if (array_key_exists(0, $res_array) AND array_key_exists(0, $res_array[0])){
			return $res_array[0][0];
		} else {
			return false;
		}
	}
	
	function query($sql){
		if ($this->debug){
			print "Querying database: '". $sql ."'";
		}
		$res = $this->_query($sql);
		$arr = $this->_result_to_array($res);
		if ($this->debug){
			print "Affected rows: ". $this->affectedRows(). " last inserted ID: ". $this->lastInsertedId();
		}
		return $arr;
	}
	
	function affectedRows(){
        if (version_compare(phpversion(), "5.3", 'lt')){
            return self::$affectedRows;
        } else {
            return $this->affectedRows;
        }
	}
	
	function lastInsertedId(){
		if (version_compare(phpversion(), "5.3", 'lt')){
            return self::$lastId;
        } else {
            return $this->lastId;
        }
	}

    function func_extract_month($colum){
        throw new DBException("Not implemented!");
    }

    function func_extract_hour($colum){
        throw new DBException("Not implemented!");
    }

    function func_extract_day($colum){
        throw new DBException("Not implemented!");
    }
}

class SQlite2Database extends DatabaseInterface{
	var $db_resource;
	protected $pk = "INTEGER PRIMARY KEY";
	
	function __construct($filename, $mode=0666){
		$err = "";
		$this->db_resource = sqlite_open($filename, $mode, $err);
		if ($this->db_resource === False){
			throw new DBException("Error on opening SQlite2 database: ". $err);
		}
		$this->_query("PRAGMA synchronous=OFF;");
	}
	
	function __destruct(){
		//sqlite_close($this->db_resource);
	}
	
	function _query($sql){
		$err = "";
		$result = sqlite_unbuffered_query($this->db_resource, $sql, SQLITE_BOTH, $err);
		if ($result === False){
			throw new DBException("Error on querying SQlite2 database: ". $err. " SQL: ".$sql);
		}
		$this->affectedRows = sqlite_changes($this->db_resource);
		$this->lastId = sqlite_last_insert_rowid($this->db_resource);
		return $result;
	}
	
	function _result_to_array($result){
		return sqlite_fetch_all($result);
	}


    function func_extract_month($column){
        return " strftime('%m', ".$column.", 'unixepoch') ";
    }

    function func_extract_hour($column){
        return " strftime('%H', ".$column.", 'unixepoch') ";
    }

    function func_extract_day($column){
        return " strftime('%d', ".$column.", 'unixepoch') ";
    }
}

class MySQLDatabase extends DatabaseInterface{
	var $db_resource;
	protected $pk = "INTEGER PRIMARY KEY AUTO_INCREMENT";
	
	function __construct($server, $login, $pass, $db){
		$err = "";
		$this->db_resource = mysql_connect($server, $login, $pass);
		if ($this->db_resource === False){
			throw new DBException("Error on opening MySQL database");
		}
        mysql_select_db($db);
	}
	
	function __destruct(){
		//sqlite_close($this->db_resource);
	}
	
	function _query($sql){
		$err = "";
		$result = mysql_query($sql, $this->db_resource);
		if ($result === False){
			throw new DBException("Error on querying MySQL database:". mysql_error($this->db_resource)." SQL: ".$sql);
		}
		self::$affectedRows = mysql_affected_rows($this->db_resource);
		self::$lastId = mysql_insert_id($this->db_resource);
		return $result;
	}
	
	function _result_to_array($result){
        if ($result === true OR $result === false){
            return array();
        } else {
            $arr = array();
            while($entry = mysql_fetch_array($result)){
                $arr[] = $entry;
            }
            return $arr;
        }
	}


    function func_extract_month($column){
        return " MONTH(FROM_UNIXTIME(".$column.")) ";
    }

    function func_extract_hour($column){
        return " HOUR(FROM_UNIXTIME(".$column.")) ";
    }

    function func_extract_day($column){
        return " DAY(FROM_UNIXTIME(".$column.")) ";
    }
}



class ModelException extends Exception{};

class Model {
	public $id;
	protected $modelData;
	protected $new = false;
	protected $needsUpdate = false;
	static public $fields = array(); // maps name of column to sql type
	static public $tableName;
	static public $tableConstrain = false;
	static protected $db = NULL;
	
	function __construct($id=False){
		//TODO: database check
		if ($id === False){
			$this->new = true;
			$this->needsUpdate = true;
		} else {
			$this->id = $id;
			$this->_load($this->_getById($this->id));
		}
	}
	
	static function setDB($db){
		Model::$db = $db;
	}
	
	function getDb(){
		return Model::$db;
	}
	
	function __get($name){
		$class_vars = get_class_vars(get_class($this));
		$tableName = $class_vars['tableName'];
		$fields = $class_vars['fields'];
		if (!array_key_exists($name, $fields)){
			throw new ModelException("Can not access $name on ". get_class($this)."! This model has no such field");
		}
		
		if (!array_key_exists($name, $this->modelData)){
			return NULL;
		} else {
			return $this->modelData[$name];
		}
	}
	
	function __set($name, $value){
		$class_vars = get_class_vars(get_class($this));
		$tableName = $class_vars['tableName'];
		$fields = $class_vars['fields'];
		if (!array_key_exists($name, $fields)){
			throw new ModelException("Can not set $name on ". get_class($this)."! This model has no such field");
		}
		$this->needsUpdate = true;
		$this->modelData[$name] = $value;
	}
	
	function __isset($name){
		return isset($this->modelData[$name]);
	}
	
	function __unset($name){
		$this->needsUpdate = true;
		unset($this->modelData[$name]);
	}
	
	function save(){
		if (!$this->needsUpdate){
			return ;
		}
		$class_vars = get_class_vars(get_class($this));
		$tableName = $class_vars['tableName'];
		$fields = $class_vars['fields'];
		
		$complete_fields = array_merge(array_fill_keys(array_keys($fields), "NULL"), $this->modelData);
		$sql = "";
		if ($this->new){
			$sql .= "INSERT INTO ";
		} else {
			$sql .= "UPDATE ";
		}
		$sql .= ' '. $tableName.' ';
		if (!$this->new){
			$sql .= " SET ";
			$sql .= ' '.array_shift(array_keys($complete_fields)).' = "'.array_shift($complete_fields).'" ';
			foreach ($complete_fields as $field=>$value){
				if ($value === "NULL"){
					$sql .= ', '.$field.' = '.$value.' ';
				} else {
					$sql .= ', '.$field.' = "'.$value.'" ';
				}
				
			}
			$sql .= " WHERE id = $this->id;";
		} else {
			$sql .= " (";
			$fields = array_keys($complete_fields);
			$sql .= ' '.array_shift($fields).' ';
			foreach ($fields as $field){
				$sql .= ', '.$field.'';
			}
			$sql .= ") VALUES ";
			$sql .= " (";
			$sql .= ' "'.array_shift($complete_fields).'" ';
			foreach ($complete_fields as $field=>$value){
				if ($value === "NULL"){
					$sql .= ', '.$value;
				} else {
					$sql .= ', "'.$value.'"';
				}
			}
			$sql .= ");";
		}
		
		$this->getDB()->query($sql);
		if ($this->new){
			$this->id = $this->getDB()->lastInsertedId();
		}
		$this->_load($this->_getById($this->id));
		$this->new = false;
		$this->needsUpdate = false;
	}
	
	function _getById($id){
        $vars = get_class_vars(get_class($this));
        $tableName = $vars['tableName'];

		return $this->getDB()->fetchOne($tableName, "id = $id");
	}
	
	function _load($raw_array){
		foreach ($raw_array as $field=>$value){
			if (is_int($field)){
				continue;
			}
			if ($field == "id"){
				$this->id = $value;
			} else {
				$this->__set($field, $value);
			}
		}
		$this->new = false;
		$this->needsUpdate = false;
	}
	
	function delete(){
		if ($this->new){
			return ;
		}
		
		$class_vars = get_class_vars(get_called_class());
		$tableName = $class_vars['tableName'];
		$fields = $class_vars['fields'];
		
		$sql = "DELETE FROM \"$tableName\" WHERE id = $this->id";
		$this->getDB()->query($sql);
		$this->id = false;
		$this->new = true;
		$this->needsUpdate = true;
	}
	
	static function isCreated(){
		$class_vars = get_class_vars(get_called_class());
		$tableName = $class_vars['tableName'];
		
		$sql = "SELECT * FROM $tableName LIMIT 1";
		try{
			Model::$db->query($sql);
		} catch (DBException $e){
			return false;
		}
		return true;
	}
	
	static function create(){
		$class_vars = get_class_vars(get_called_class());
		$tableName = $class_vars['tableName'];
		$fields = $class_vars['fields'];
		$tableConstrain = $class_vars['tableConstrain'];
		
		$sql = "CREATE TABLE $tableName (";
		
		$sql .= ' id '.Model::$db->getPrimaryKeyType().' ';
		foreach ($fields as $field=>$value){
			$sql .= ", $field $value";
		}
		
		if ($tableConstrain !== False){
			$sql .= ",  $tableConstrain);";
		} else {
			$sql .= ") ;";
		}
		
		Model::$db->query($sql);
		
	}
	
	static function createIfNecessary(){
		$class = get_called_class();
		if (!self::isCreated()){
			self::create(); 
		}
	}
	
	static function getOne($where="1"){
		$class_vars = get_class_vars(get_called_class());
		$tableName = $class_vars['tableName'];
		$fields = $class_vars['fields'];
		
		$sql = "SELECT id FROM $tableName WHERE $where LIMIT 1;";
		
		$class = get_called_class();
		$ids = Model::$db->query($sql);
		if (array_key_exists(0, $ids)){
			return new $class($ids[0]['id']);
		}
		
		return false;
	}
	
	static function getAll($where="1"){
		$class_vars = get_class_vars(get_called_class());
		$tableName = $class_vars['tableName'];
		$fields = $class_vars['fields'];
		
		$sql = "SELECT id FROM $tableName WHERE $where;";
		
		$class = get_called_class();
		$results = array();
		$ids = Model::$db->query($sql);
		foreach ($ids as $next){
			$results[$next['id']] = new $class($next['id']);
		}
		
		return $results;
	}
	
	static function count($where="1"){
		$class_vars = get_class_vars(get_called_class());
		$tableName = $class_vars['tableName'];
		$fields = $class_vars['fields'];
		
		$sql = "SELECT count(id) FROM $tableName WHERE $where;";
		
		return Model::$db->getCol($sql);
	}
}

?>
