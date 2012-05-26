<?php
require_once 'page.php';

$start = $_GET['start'];

$all_rows = $db->query("select * from xld_1 limit $start,10000;");

$_POST['SESSION'] = 'klyxun';
$_POST['ACCOUNT'] = 'test';
$_POST['WAY'] = 2;

$i = $start;
$i++;

foreach ($all_rows as $row){
	$_POST['TIME'] = strtotime($row['DateTime']);
	$_POST['DCU'] = $row['DCU'];
	$_POST['DCP'] = $row['DCP'];
	$_POST['ACP'] = $row['ACP'];
	$_POST['TEMP'] = $row['TEMP'];
	$_POST['SC'] = $row['SC'];
        require('upload.php');
	$i++;
	print $i;print '</br>';
}