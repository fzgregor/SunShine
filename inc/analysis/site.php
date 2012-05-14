<?php
require_once 'page.php';

$view = new View();
$html = '
<h1>Auswertung - Beta</h1>
<p>Auf dieser Seite werden die Leistungsdaten der gewählten Anlage in einem Diagramm angezeigt.</p>
<p>Mit ihrer Maus können sie ausgewählte Werte vergrößern. Klicken sie dazu mit der linken Maustaste auf den Datenbereich des Diagramms, ziehen sie die Maus über die zu vergrößernden Werte und lassen anschließend die linke Maustaste wieder los. Der Bereich wird nun vergrößert. Der ursprüngliche Wertebereich kann durch klicken auf "Reset Zoom" wieder angezeigt werden.</p>
<p>Mit dem Symbolen oben rechts können sie das aktuelle Diagramm drucken oder als Datei abspeichern.</p>
<div id="form_model" style="display:none">
	<fieldset>
	<legend>Datensatz</legend>
        <div class="subcolumns">
            <div class="c40l">
		        <fieldset>
		        <legend>Anlage</legend>
		            <div class="type-select">
		                <select name="plant_ids[]">';
			        foreach(Plant::getIdToNameArray() as $id=>$name){
				        $html .= '<option value="'.$id.'">'.$name.'</option>';
			        }
			        $html .= '</select>
		            </div>
		        </fieldset>
		        <fieldset>
		        <legend>Zeitpunkt</legend>
		            <div class="type-text">
		                <input name="times[]" type="text" />
		            </div>
		        </fieldset>
            </div>
            <div class="c60r">
		        <fieldset>
		            <legend>Datenreihen</legend>
		            <div class="type-select">
                        <select name="replace_with_plant_id" multiple="multiple" style="height:9.7em;">
                            <option value="dcu">Gleichspannung</option>
                            <option value="dcp">Gleichspannungsleistung</option>
                            <option value="dci">Gleichspannungstrom</option>
                            <option value="acp">Wechselspannungsleistung</option>
                            <option value="temperature">Modultemperatur</option>
                            <option value="irradiation">Einstrahlung</option>
                        </select>
                    </div>
		        </fieldset>
            </div>
        </div>
	</fieldset>
</div>
<script>
    var index = 0;

	function add(){
		$( "#form_model > fieldset" ).clone().appendTo( "#target" );
        $("#target").children().last().find("select").last().multiselect({searchable: false});
        $("#target").children().last().find("input").datepicker();
        $("#target").children().last().find(\'select[name!="plant_ids[]"]\').attr("name", "series_"+index+"[]");
        index++;
	}

	$(document).ready(function(){add();});

	function loadChart(){
        $.ajax({
            "url":"analysis_ajax_chart.php",
            "type":"POST",
            "data":$("form").serialize(),
            "dataType":"html",
            "success":function (data, txtState, jqXHR){$("#chart").html(data);},
            });
		return false;
	}
</script>

<form class="yform full" method="GET">
<fieldset id="target">
<legend>Zeitraum</legend>
	<div class="type-select">
		<select name="space" id="space">
			<option value="day">ein Tag</option>
			<option value="month">ein Monat</option>
			<option value="year">ein Jahr</option>
		</select>
	</div>
</fieldset>
<div class="type-button">
    <input type="button" onClick="add()" value="weiterer Datensatz" />
    <input type="submit" onClick="return loadChart(this)" value="Diagramm laden" />
</div>
<div id="chart"></div>
<div id="table"></div>
</form>';

$view->fill_subview_with_html("CONTENT", $html);
$view->render();
?>
