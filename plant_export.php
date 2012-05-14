<?php
require_once 'page.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $head = $_POST["head"]?true:false;
    $start = strtotime($_POST["start"]);
    $end = strtotime($_POST["end"]) + strtotime("+1 day", 0);
    $plant = isset($_POST['plant']) ? $_POST['plant'] : 1;
    
    if (!$head){
        $results = "";
    } else {
        $results = "Zeitpunkt;U_DC[V];P_DC[W];P_AC[W];T_Mod[C°];G[W/m²]\n";
    }
    
    $plant = new Plant($plant);
    $raw_date = $plant->getMeasurementDataBetween($start, $end);
    
    foreach ($raw_date as $measure){
       $results .= date("H:i d.m.Y", $measure["time"]).";";
       $results .= str_replace(".", ",", $measure["dcu"].";".$measure["dcp"].";".$measure["acp"].";".$measure["temperature"].";".$measure["irradiation"]."\n");
    }

    $filename = $plant->name. "_export.csv";
    
    $dodgychars = "[^0-9a-zA-z()_-]";
    $filename = preg_replace("/$dodgychars/","_",$filename);
    
    header("Content-Type: text/x-csv");
    header("Content-Disposition: attachment; filename=$filename"); 
    header("Content-Description: csv File" ); 
    print ($results);
    exit();
}

$view = new View("MainView.html");
$html = "
<h1>Datenexport</h1>
<p>Sie wollen Ihre eigenen Auswertungen durchführen oder Statistiken erstellen?</p>
<p>Hier haben Sie die Möglichkeit die Leistungsdaten im Rohformat herunterzuladen.</p>
<p>Wählen Sie einfach die gewünschte Anlage und den Anfangs- sowie den Endpunkt der benötigten Daten ein.</p>
";

$html .= '
<script>
	$(function() {
		$( "#start" ).datepicker({ "altFormat": "dd.mm.yy" });
		$( "#end" ).datepicker({ "altFormat": "dd.mm.yy" });
	});
</script>

<form class="yform full" method="POST">
<fieldset>
<legend>Anlage</legend>
    <div class="type-select">
        <select id="plant" name="plant">
';

foreach(Plant::getIdToNameArray() as $id=>$name){
	if (currentPlant() == $id){
		$html .= '<option selected="selected" value="'.$id.'">'.$name.'</option>';
	} else {
		$html .= '<option value="'.$id.'">'.$name.'</option>';
	}
}

$html .= '        </select>
    </div>
</fieldset>
<fieldset>
      <legend>Zeitraum</legend>
        <div class="type-text">
          <label for="start">Start</label>
          <input type="text" id="start" name="start"/>
          <label for="end">Ende</label>
          <input type="text" id="end" name="end"/>
        </div>
</fieldset>
<fieldset>
    <legend>Sonstiges</legend>
    <div class="type-check">
        <input id="head" name="head" type="checkbox" checked="checked" value="true"/> Tabellenkopf ausgeben
    </div>
</fieldset>
<div class="type-button">
    <input type="submit" value="Herunterladen" />
</div>
</form>';

$view->fill_subview_with_html("CONTENT", $html);
$view->render();
?>
