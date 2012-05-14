<?php
class Chart{
    var $height;
    var $width = "100%";
    var $interval;
    var $start_point;
    var $title = null;
    var $subtitle = null;
    var $credits = array("SunShine", "http://www.sn.schule.de/~solar/SunShine");
    var $exporting = true;
    // contains the data
    // these are arrays of the form array(name of the series, unit for axis, type of plotting (line, bar...), series array of values )
    var $series = array();
    var $y_axisUnitToNumber = array();
    var $y_axisUnitToName = array();

    static private $first_time = true;

    function Chart($height="100%", $width="100%", $startpoint=0, $interval=0){
        $this->width = $width;
        $this->height = $height;
        $this->interval = $interval;
        $this->start_point = $startpoint;
    }

    function title($title){
        $this->title = $title;
    }

    function subtitle($subtitle){
        $this->subtitle = $subtitle;
    }

    function credits($credits){
        $this->credits = $credits;
    }

    function exporting($ex){
        $this->exporting = $ex ? true : false;
    }

    function addSeries($name, $unit, $type, $series){
    	// replace NULL with "null"
    	for($i = 0; $i < sizeof($series); $i++){
    		if (is_null($series[$i])){
    			$series[$i] = "null";
    		}
    	}
        array_push($this->series, array($name, $unit, $type, $series));
    }

    function calcYAxis(){
        $i = 0;
        foreach($this->series as $serie){
            if (array_key_exists($serie[1],$this->y_axisUnitToNumber)){
                $number = $this->y_axisUnitToNumber[$serie[1]];
                $this->y_axisUnitToName[$serie[1]] = $this->y_axisUnitToName[$serie[1]]." / ".$serie[0];
            } else {
                $this->y_axisUnitToNumber[$serie[1]] =  $i;
                $this->y_axisUnitToName[$serie[1]] = $serie[0];
                $number = $i;
                $i++;
            }
            
        }
    }

    function html(){
        $html = "
            <script type=\"text/javascript\">";
        if (Chart::$first_time){
            $html .= "Highcharts.setOptions({global: {useUTC: false}});";
            Chart::$first_time = false;
        }
        $html .= "
            $(document).ready(function() {
               var chart = new Highcharts.Chart({
                  chart: {
                     renderTo: '$this->title$this->subtitle',
                     zoomType: 'x',
                     shadow: true,
                  },";
        // title
        if (!is_null($this->title)){
            $html .= "
              title: {
                 text: '$this->title'
              },";
        }
        // subtitle
        if (!is_null($this->subtitle)){
            $html .= "
              subtitle: {
                 text: '$this->subtitle'
              },";
        }
        // credit line
        if (!is_null($this->credits)){
            $t = $this->credits[0];
            $l = $this->credits[1];
            $html .= "
              credits: {
                 text: '$t',
                 href: '$l',
              },";
        }
        // enable export shortcuts
        if ($this->exporting){
            $html .= "
              exporting: {
                 enabled: true
              },";
        }
        // create xAxis
        $html .= "
            xAxis: {
                type: 'datetime',
                maxZoom: $this->interval * 1000,
            },";
        // create yAxis
        # yAxis begin
        $this->calcYAxis();
        $html .= "
          yAxis: [";
        $opposite = false;
        foreach ($this->y_axisUnitToNumber as $unit => $number){
        	$o = $opposite ? "true" : "false";
            $name = $this->y_axisUnitToName[$unit];
            $html .= "
     {
         title: {
             text: '$name',
         },
         labels: {
             formatter: function() {
                 return this.value +' ".$unit."';
             },
         },
         opposite: ".$o.",
     },";
        $opposite = $opposite == false;
        }
        # yAxis end
        $html .= "
          ],
        ";
        
        // disable markers
        $html .= "plotOptions: {
        series: {
            enableMouseTracking: false,
            stacking: null,
            marker: {
                enabled: false,
                states: {
                    hover: {
                        enabled: true
                    }
                }
            },
            pointStart:  ".$this->start_point." * 1000,
            pointInterval: ".$this->interval." * 1000, // millisecs
        },
    },";

        // data series
        // begin
        $html .= "series: [";
        foreach($this->series as $serie){
            $axis = $this->y_axisUnitToNumber[$serie[1]];
            $html .= "
              {
                 name: '".$serie[0]." in ".$serie[1]."',
                 type: '".$serie[2]."',
                 data: [".implode(",", $serie[3])."],
                 yAxis: $axis,
              
              },";
        }
        // end
        $html .= "],";
        
        //tooltip begin
        $html .= "tooltip:{
        	formatter: function() {
            var s = '<b>'+ Highcharts.dateFormat('%d.%m.%Y %H:%M', this.x) +'</b>';
           
            $.each(this.points, function(i, point) {
                s += '<br/><span style=\"color:'+point.series.color+';\">'+ point.series.name +'</span>: '+
                    point.y;
            });
            
            return s;
        },
        
        shared: true,
        crosshairs: true,";
        
        
        
        //tooltip end
        $html .= "},";

        $html .= "});});";
        

        $html .= "</script>";
        $html .= '<div id="'.$this->title.$this->subtitle.'"></div>';
        return $html;
    }
}

?>
