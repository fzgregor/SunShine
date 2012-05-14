<?php
$menu = array(
    array("Home", "index.php"),
    array("Das Projekt", "project.php"),
    array("Anlagen", array(
        array("Übersicht", "plant_overview.php"),
        array("Datenexport", "plant_export.php"),
        array("Datenvisualisierung", "plant_chart.php"),
        )
    ),
    array("Auswertung", "analysis.php"),
    array("Impressum", "imprint.php")
);

function inc($mark){
    $mark[strlen($mark)-2] = $mark[strlen($mark)-2] + 1;
    return $mark;
}

function app($mark){
    $mark .= "1.";
    return $mark;
}

function rem($mark){
    return substr($mark, 0, strlen($mark)-2);
}
?>


<ul class="vlist">
<?
$toc = $menu;
$mark = app("");
$parent = array();
while ($cur = array_shift($toc)){
    $name = $cur[0];
    $link = $cur[1];

    if (is_array($link)){
        echo '<li><a href="'.$link[0][1].'">'.$mark." ".$name.'</a><ul>';
        array_push($parent, $toc);
        $toc = $link;
        $mark = app($mark);
    } else {
        echo '<li><a href="'.$link.'">'.$mark." ".$name.'</a></li>';
        $mark = inc($mark);
    }
    if (count($toc) == 0 && count($parent) > 0){
        $toc = array_pop($parent);
        $mark = inc(rem($mark));
        echo '</ul>';
    } else {
    }
}
?>
</ul> 
