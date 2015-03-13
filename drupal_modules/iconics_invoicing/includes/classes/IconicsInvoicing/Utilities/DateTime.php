<?php

namespace IconicsInvoicing\Utilities;

use DateTime as PHPDateTime;

/**
 * Custom DateTime extensions.
 *
 * @date 2014-12-15
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 */
class DateTime extends PHPDateTime
{
    const MYSQL_DATE_FORMAT     = 'Y-m-d';
    const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Will return a modified DateTime object while not touching this object.
     *
     * @param $modificiation See DateTime::modify for valid inputs.
     *
     * @return DateTime
     */
    public function getModifiedDateTime($modification)
    {
        $newDate = clone $this;

        return $newDate->modify($modification);
    }

    /**
     * Will return the difference in days.
     *
     * @return int
     */
    public function getDiffInDays(PHPDateTime $date)
    {
        $interval = $this->diff($date);

        return (int) $interval->days;
    }

    /**
     * Check if another date is same as this one.
     *
     * @return boolean
     */
    public function isSameDay(PHPDateTime $date)
    {
        $dateToCheck = clone $date;
        $dateToCheck->setTime(0, 0);

        $thisDate = clone $this;
        $thisDate->setTime(0, 0);

        return $dateToCheck == $thisDate;
    }
}
