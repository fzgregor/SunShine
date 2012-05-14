<?php
require_once 'page.php';

$plant = new Plant(currentPlant());
$html = "
<h1>Anlagendetails - $plant->name</h1>
<h2>Installation</h2>
<table class=\"full\">
    <thead>
        <tr>
            <th>im Betrieb seit</th>
            <th>Datenerfassung seit</th>
            <th>installierte Leistung</th>
            <th>Panelfläche</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>".Date("d.m.Y", $plant->build_date)."</td>
            <td>".Date("d.m.Y H:i", $plant->captured_since)."</td>
            <td>".$plant->power_peak." W<sub>peak</sub></td>
            <td>".$plant->panel_area." m²</td>
        </tr>
    </tbody>
</table>
<h2>Aktuelle Daten</h2>
<div class=\"subcolumns\">
    <div class=\"c50l\">
        <div class=\"subcl\">
            <h3>Leistungsdaten</h3>
            <table class=\"full\">
                <thead>
                    <tr>
                        <th>Zeitraum</th>
                        <th style=\"text-align:right\">Leistung</th>
                    </tr>
                </thead>
                <tbody>";
$lines = array(
    array("Heute", humanNumber($plant->getPowerToday())),
    array("Gestern", humanNumber($plant->getPowerYesterday())),
    array("Diese Woche", humanNumber($plant->getPowerThisWeek())),
    array("Letzte Woche", humanNumber($plant->getPowerLastWeek())),
    array("Dieser Monat", humanNumber($plant->getPowerThisMonth())),
    array("Letzter Monat", humanNumber($plant->getPowerLastMonth())),
    array("Dieses Jahr", humanNumber($plant->getPowerThisYear())),
    array("Letztes Jahr", humanNumber($plant->getPowerLastYear())),
    array("Gesamt", humanNumber($plant->getPowerOverall())),
);
foreach ($lines as $line){
    $html .= "<tr><td>".$line[0]."</td><td style=\"text-align:right\">".$line[1]." W</td></tr>";
}
$html .= "                </tbody>
            </table>
        </div>
    </div>
    <div class=\"c50r\">
        <div class=\"subcl\">
            <h3>Vollständigkeit</h3>
            <table class=\"full\">
                <thead>
                    <tr>
                        <th>Zeitraum</th>
                        <th style=\"text-align:right\">Datenvollständigkeit</th>
                    </tr>
                </thead>
                <tbody>";
$lines = array(
    array("Heute", humanNumber($plant->getQualityOfServiceToday())),
    array("Gestern", humanNumber($plant->getQualityOfServiceYesterday())),
    array("Diese Woche", humanNumber($plant->getQualityOfServiceThisWeek())),
    array("Letzte Woche", humanNumber($plant->getQualityOfServiceLastWeek())),
    array("Dieser Monat", humanNumber($plant->getQualityOfServiceThisMonth())),
    array("Letzter Monat", humanNumber($plant->getQualityOfServiceLastMonth())),
    array("Dieses Jahr", humanNumber($plant->getQualityOfServiceThisYear())),
    array("Letztes Jahr", humanNumber($plant->getQualityOfServiceLastYear())),
    array("Gesamt (ab Erfassung)", humanNumber($plant->getQualityOfServiceOverall())),
);
foreach ($lines as $line){
    $html .= "<tr><td>".$line[0]."</td><td style=\"text-align:right\">".$line[1]." %</td></tr>";
}
$html .= "                </tbody>
            </table>
        </div>
    </div>
</div>
";

// view setup
$view = new View("MainView.html");
$view->fill_subview_with_html("CONTENT", $html);
$view->render();
?>
