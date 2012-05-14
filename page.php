<?php
require_once 'database/models.php';
require_once '#configure.php';
require_once 'inc/View.php';
require_once 'inc/Chart.php';
require_once 'inc/Statistics.php';
require_once 'inc/Helper.php';
require_once 'inc/MeasurementData.php';

# PHP 5.2 hack
require_once 'inc/get_called_class.php';

function initialise(){
	require_once 'initialise.php';
}

function currentPlant(){
    session_start();
    if (isset($_REQUEST["plant"])){
        $_SESSION["plant"] = $_REQUEST["plant"];
        return $_SESSION["plant"];
    } else if (isset($_SESSION["plant"])){
        return $_SESSION["plant"];
    } else {
        return 1;
    }
}
?>
