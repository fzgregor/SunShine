<?php
require_once 'page.php';

$start = $_GET['start_row'];

$all_rows = $db->query("select * from xld_1 order by ID limit $start,20000;");

$_POST['SESSION'] = 'afz4ih';
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