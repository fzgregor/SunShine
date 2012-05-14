<?php
require_once 'page.php';

initialise();

$view = new View("MainView.html");
$html = "
<h1>Herzlich Willkommen auf der neuen Seite von ~solar</h1>
<p>Diese Seite (<a href=\"www.sn.schule.de/~solar/SunShine\">www.sn.schule.de/~solar/SunShine</a>) soll die alter Seite unter <a href=\"www.sn.schule.de/~solar\">www.sn.schule.de/~solar</a> in naher Zukunft ablösen.</p>
<p><strong>Wenn ihnen Fehler auffallen oder sie Verbessungsvorschläge haben, melden sie sich doch bitte bei mir unter rentafranzATgmailPUNKTcom. Danke im Vorraus.</strong></p>
<p>TODO:</p>
<ul>
	<li>jQuery, Highcharts and DataTables to footer <i>DONE</i></li>
    <li>Better DataTables logo</li>
</ul>";

$view->fill_subview_with_html("CONTENT", $html);
$view->render();
?>
