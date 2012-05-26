<?php
require_once 'page.php';

initialise();

$view = new View("MainView.html");
$html = "
<h1>Herzlich Willkommen auf SunShine</h1>
<p>Auf dieser Seite finden sie Informationen über mein Projekt zur Datenerfassung von Photovoltaikanlagen. Sowie die aktuellen Daten der Anlage(n).</p>
<p><strong>Sie sind Besitzer einer Photovoltaikanalage von Siemens Marke Sitop?</strong></p>
<p>Schreiben Sie mir doch eine Mail, ich habe noch 3 Datenlogger günstig(gratis) abzugeben.</p>
<p><strong>Wenn ihnen Fehler auffallen oder sie Verbessungsvorschläge haben, melden sie sich doch bitte bei mir unter rentafranzATgmailPUNKTcom. Danke im Vorraus.</strong></p>
";
$view->fill_subview_with_html("CONTENT", $html);
$view->render();
?>
