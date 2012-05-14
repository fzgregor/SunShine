<?php
function thisWeek(){
    $year = Date("Y");
    $week = Date("W");
    return strtotime($year."W".$week."1");
}

function lastWeek(){
    return thisWeek() - 60 * 60 * 24 * 7;
}

function thisMonth(){
    return mktime(0, 0, 0, Date("m"), 1, Date("Y"));
}

function lastMonth(){
    return strtotime("-1 month", thisMonth());
}

function thisYear(){
    return mktime(0, 0, 0, 1, 1, Date("Y"));
}

function lastYear(){
    return mktime(0, 0, 0, 1, 1, Date("Y") - 1);
}

function humanNumber($num, $unit=false){
    if ($unit){
        $dimensions = array('', 'k', 'M', 'T', 'P');
        foreach($dimensions as $dimension){
            if ($num <= 800){
                return number_format($num, 2, ",", "'"). " $dimension$unit";
            } else {
                $num = $num / 1000;
            }
        }
    } else {
        return number_format($num, 2, ",", "'");
    }
}
