<?php
require_once ("./inc/analysis/common.php");

$ad = new AnalysisData(space(), plants(), times(), series());
$ac = new AnalysisChart($ad);
print $ac->getHtml();

class AnalysisChart{
    private $chart;

    function AnalysisChart($analysis_data){
        if ($analysis_data->oneTime()){
            $this->chart = new Chart("100%", "100%", $analysis_data->last_start, $analysis_data->interval);
        } else {
            $this->chart = new Chart("100%", "100%", 0, $analysis_data->interval);
        }

        foreach($analysis_data->series as $series){
            $this->chart->addSeries($series["name"], $series["unit"], $series["chart_type"], $series["series"]);
        }

        $this->chart->title("Vergleich");
        $this->chart->subtitle($analysis_data->subtitle);
    }

    function getHtml(){
        return $this->chart->html();
    }
}
