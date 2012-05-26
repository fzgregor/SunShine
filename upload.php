<?php
require_once 'page.php';

switch(isset($_POST["WAY"]) ? $_POST["WAY"] : 4){
    case 1:
        $login = isset($_POST["ACCOUNT"]) ? $_POST["ACCOUNT"] : "";
        $password = isset($_POST["KEY"]) ? $_POST["KEY"] : "";
        $plant = Plant::getOne(" login = \"$login\" AND password = \"$password\"");
        if ($plant === false){
        	print ("E : The login doesn't exist or the password is wrong.;\n");
        	exit();
        } else {
        	$session = Session::openNewSessionFor($plant);
        	print ("S : $session->session_id;\n");
        }
        break;
    case 2:
    	$login = isset($_POST["ACCOUNT"]) ? $_POST["ACCOUNT"] : "";
    	$session = isset($_POST["SESSION"]) ? $_POST["SESSION"] : "";
    	
        $time = isset($_POST["TIME"]) ? $_POST["TIME"] : "";
        $dcu = isset($_POST["DCU"]) ? $_POST["DCU"] : "";
        $dcp = isset($_POST["DCP"]) ? $_POST["DCP"] : "";
        $acp = isset($_POST["ACP"]) ? $_POST["ACP"] : "";
        $temperature = isset($_POST["TEMP"]) ? $_POST["TEMP"] : "";
        $irradiation = isset($_POST["SC"]) ? $_POST["SC"] : "";
        
        $session = Session::getSessionWithId($session);
        $plant = Plant::getOne(" login = \"$login\" ");
        
        if (!$session OR !$plant OR $session->plant != $plant->id){
        	print ("E : The login doesn't exist or the session is wrong.;\n");
        	exit();
        } else {
        	$measurement = Measurement::getMeasurementFor($plant, $time);
        	try{
	        	$measurement->addNewMeasurement(array(
	        		"time" => $time,
	        		"acp" => $acp,
	        		"dcu" => $dcu,
	        		"dcp" => $dcp,
	        		"temperature" => $temperature,
	        		"irradiation" => $irradiation,
	        	));
	        	
	        	$measurement->save();
        		print ("O: Uploaded;\n");
	        	$session->upload_count += 1;
	        	$session->save();
        		//exit();
        	} catch (ModelException $e){
        		switch ($e->getCode()) {
        			case 1:
        				print ("E : Programming error measurement->add() doesn't have enough data;\n");
        				exit();
        			case 2:
        				print ("E : Programming error won't add measure to wrong measurement (time);\n");
        				exit();
        			case 3:
        				print ("D: Duplicated;\n");
        				//exit();
					break;
        			default:
        				throw $e;
        		}
        	}
        }
        break;
    case 3:
    	$session = isset($_POST["SESSION"]) ? $_POST["SESSION"] : "";
        $session = Session::getSessionWithId($session);
        if ($session === false){
            print ("E: Could not close Session;\n");
            exit();
        } else {
            $session->close();
            print ("O: Session closed;\n");
            exit();
        }
        break;
    default:
        print ("E : Wrong WAY value!;\n");
}
?>
