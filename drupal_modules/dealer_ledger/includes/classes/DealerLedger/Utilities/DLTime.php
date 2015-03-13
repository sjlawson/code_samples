<?php

namespace DealerLedger\Utilities;

class DLTime
{
    const FIRST=           1;
    const LAST=            2;
    const DAYS_IN_MONTH=   3;
    const FIRST_NEXT_MONTH=4;
    const MONTHS_BEFORE=   5;
    const DAYS_BEFORE=     6;
    const YEARS_BEFORE=    7;
    const YEARS_AFTER=     8;
    const MONTHS_AFTER=    9;
    const DAYS_AFTER=      10;
    const DATE_PICKER_TIME=11;
    const DATE_PICKER=     12;
    const SQL_TIME=        13;
    const DAY_OF_MONTH=    14;
    const YEAR=            15;
    const SQL=             16;
    const MONTH_NUMBER=    17;

    public static function getDate($string=null, $option=null, $amount=0)
    {
        //change dashes to slashes
        $string=str_replace('-', '/', $string);

        //if it evals to zero, use todays date
        if (!$unixTime=strtotime($string)) {
            $unixTime=strtotime("now");
        }

        switch ($option) {
            case self::FIRST:
                return date("Y-m-1");
            case self::LAST:
                return date("Y", $unixTim)."-".date("m", $unixTime)."-".date("d", mktime(0, 0, 0, (date("m", $unixTime)+1), 0, date("Y", $unixTime)));
            case self::DAYS_IN_MONTH:
                return date("d", mktime(0, 0, 0, (date("m", $unixTime)+1), 0, date("Y", $unixTime)));//figure the last day of the month
            case self::FIRST_NEXT_MONTH:
                return date("Y-m-1", mktime(0, 0, 0, (date("m", $unixTime)+2), 0, date("Y", $unixTime)));
            case self::YEARS_BEFORE:
                return date("Y-m-d", mktime(0, 0, 0, date("m", $unixTime), date("d", $unixTime), (date("Y", $unixTime)-$amount)));
            case self::MONTHS_BEFORE:
                return date("Y-m-d", mktime(0, 0, 0, (date("m", $unixTime)-$amount), date("d", $unixTime), date("Y", $unixTime)));
            case self::DAYS_BEFORE:
                return date("Y-m-d", mktime(0, 0, 0, date("m", $unixTime), (date("d", $unixTime)-$amount), date("Y", $unixTime)));
            case self::YEARS_AFTER:
                return date("Y-m-d", mktime(0, 0, 0, date("m", $unixTime), date("d", $unixTime), (date("Y", $unixTime)+$amount)));
            case self::MONTHS_AFTER:
                return date("Y-m-d", mktime(0, 0, 0, (date("m", $unixTime)+$amount), date("d", $unixTime), date("Y", $unixTime)));
            case self::DAYS_AFTER:
                return date("Y-m-d", mktime(0, 0, 0, date("m", $unixTime), (date("d", $unixTime)+$amount), date("Y", $unixTime)));
            case self::DATE_PICKER_TIME:
                return date("m/d/Y H:i:s", $unixTime);
            case self::DATE_PICKER:
                return date("m/d/Y", $unixTime);
            case self::SQL_TIME:
                return date("Y-m-d H:i:s", $unixTime);
            case self::DAY_OF_MONTH:
                return date("d", $unixTime);
            case self::YEAR:
                return date("Y", $unixTime);
            case self::MONTH_NUMBER:
                return date("m", $unixTime);
            case self::SQL:
            default:
                return date("Y-m-d", $unixTime);
        }
    }

}
