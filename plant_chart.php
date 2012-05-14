<?php
require_once 'page.php';

$plant = new Plant(currentPlant());
$day = array_key_exists("day", $_REQUEST) ? $_REQUEST["day"] : date("d.m.Y");
$md = new MeasurementData($plant);
$md->setTime(strtotime($day));
$md->setSpace(DAY);
$md->query();

$dcp = array();
$acp = array();
$dcu = array();
$temperature = array();
$irradiation = array();
$start = false;
while ($md->setNext()){
    ($start === false) ? $start = $md->time : true;
	$dcp[] = $md->dcp;
	$acp[] = $md->acp;
	$dcu[] = $md->dcu;
	$temperature[] = $md->temperature;
	$irradiation[] = $md->irradiation;
}

$chart = new Chart("100px", "100%", $start, 60*60);
$chart->title($plant->name);
$chart->subtitle("Der Tag ". date("d.m.Y", $start));
$chart->addSeries("Gleichstromleistung", "W", "column", $dcp);
$chart->addSeries("Wechselstromleistung", "W", "column", $acp);
$chart->addSeries("Gleichstromspannung", "V", "line", $dcu);
$chart->addSeries("Modultemperatur", "Cº", "line", $temperature);
$chart->addSeries("Einstrahlung", "W/m²", "line", $irradiation);



$view = new View("MainView.html");
$html = '
<h1>Datenvisualisierung</h1>
<p>Auf dieser Seite werden die Leistungsdaten der gewählten Anlage in einem Diagramm angezeigt.</p>
<p>Mit ihrer Maus können sie ausgewählte Werte vergrößern. Klicken sie dazu mit der linken Maustaste auf den Datenbereich des Diagramms, ziehen sie die Maus über die zu vergrößernden Werte und lassen anschließend die linke Maustaste wieder los. Der Bereich wird nun vergrößert. Der ursprüngliche Wertebereich kann durch klicken auf "Reset Zoom" wieder angezeigt werden.</p>
<p>Mit dem Symbolen oben rechts können sie das aktuelle Diagramm drucken oder als Datei abspeichern.</p>
</br>
';


// chart
$html .= $chart->html();

// date & plant selector
$html .= '
</br>
<script>
	$(function() {
		$( "#day" ).datepicker();
	});
</script>

<form class="yform full" method="POST">
<fieldset>
<legend>Anlage</legend>
    <div class="type-select">
        <select onChange="submit()" id="plant" name="plant">
';

foreach(Plant::getIdToNameArray() as $id=>$name){
	if (currentPlant() == $id){
		$html .= '<option selected="selected" value="'.$id.'">'.$name.'</option>';
	} else {
		$html .= '<option value="'.$id.'">'.$name.'</option>';
	}
}

$html .= '</select>
    </div>
</fieldset>
<fieldset>
      <legend>Zeitraum</legend>
        <div class="type-text">
          <label for="day">Tag</label>
          <input onChange="submit()" type="text" id="day" name="day" value="'.$day.'"/>
        </div>
</fieldset>
<div class="type-button">
    <input type="submit" value="Diagramm laden" />
</div>
</form>';

$view->fill_subview_with_html("CONTENT", $html);
$view->render();
?>
