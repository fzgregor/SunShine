<?php
require_once 'page.php';


$view = new View("MainView.html");

$html = "
<script type=\"text/javascript\">
$(document).ready(function() {
	$('#plants').dataTable({
		'bStateSave': true,
		'sDom': '<\"floatbox\"<\"filter\"f>>t',
		'aoColumns': [
			null,
			null,
			null,
			null,
			null,
			null
		],
		'sPaginationType': 'full_numbers',
		'oLanguage':{
			'sProcessing':   'Bitte warten...',
			'sLengthMenu':   '_MENU_ Einträge anzeigen',
			'sZeroRecords':  'Keine Einträge vorhanden.',
			'sInfo':         '_START_ bis _END_ von _TOTAL_ Einträgen',
			'sInfoEmpty':    '0 bis 0 von 0 Einträgen',
			'sInfoFiltered': '(gefiltert von _MAX_  Einträgen)',
			'sInfoPostFix':  '',
			'sSearch':       'Suche',
			'sUrl':          '',
			'oPaginate': {
				'sFirst':    'Erster',
				'sPrevious': 'Zurück',
				'sNext':     'Nächster',
				'sLast':     'Letzter'
	}
}
	});
} );
</script>
<style type=\"text/css\">
.filter {
	float:right;
}
</style>
";

$html .= "
<h1>Die Anlagen</h1>
<table id=\"plants\" class=\"full\">
    <thead>
        <tr>
            <th>Name</th>
            <th>Gebaut seit</th>
            <th>Erfassung seit</th>
            <th>Leistung</th>
            <th>Panelfläche</th>
            <th>Details</th>
        </tr>
    </thead>
    <tbody>
";

foreach (Plant::getAll() as $plant){
    $html .= "
    <tr>
    	<td>$plant->name</td>
    	<td>".date("d.m.Y", $plant->build_date)."</td>
    	<td>".date("d.m.Y", $plant->captured_since)."</td>
    	<td>".humanNumber($plant->power_peak, "W")."</td>
    	<td>$plant->panel_area m²</td>
    	<td><a href=\"plant_detail.php?plant=$plant->id\">Details</a></td>
    </tr>";
}

$html .= "
    </tbody>
</table>";

$view->fill_subview_with_html("CONTENT", $html);
$view->render();
?>
