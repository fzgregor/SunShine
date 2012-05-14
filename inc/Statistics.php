<?php

class Statistics{
    static function db(){
        return Model::getDb();
    }

    static function PowerBetween($start, $end){
        $sql = "
            SELECT
                IFNULL(ROUND(SUM(acp)/4,2), 0)
            FROM
                measurement m 
            LEFT JOIN 
                day d ON (d.id = m.day)
            WHERE
                d.day + m.time >= $start AND
                d.day + m.time < $end;";
        
        return self::db()->getCol($sql);
    }

    static function PowerToday(){
        return self::PowerBetween(strtotime("today"), time());
    }
    
    static function PowerOverall(){
        return self::PowerBetween(0, time());
    }

    static function PeakOverall(){
        $sql = "SELECT SUM(power_peak) FROM plant;";
        
        return self::db()->getCol($sql);
    }

}
