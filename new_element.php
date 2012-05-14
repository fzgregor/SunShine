<?php
require_once 'page.php';

$newPlant = new Plant();
$newPlant->name = "Pestalozzi Gymnasium Dresden";
$newPlant->build_date = strtotime("21.11.2003");
$newPlant->login = "test";
$newPlant->password = "test";
$newPlant->power_peak = 1700;
$newPlant->panel_area = 13.8;//8*;
//$newPlant->save();

?>
