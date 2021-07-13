<?php

namespace My\Events\Posts;

use My\Events\Model;

class Event extends Post
{
    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getField('description');
    }

     /**
     * Get start time
     *
     * @param string $format
     * @return (DateTime|string)
     */
    public function getStartTime($format = null)
    {
        $timezone = new \DateTimeZone(wp_timezone_string());
        $time     = new \DateTime($this->getField('start'), $timezone);

        return $format ? $time->format($format) : $time;
    }

    /**
     * Get end time
     *
     * @param string $format
     * @return (DateTime|string)
     */
    public function getEndTime($format = null)
    {
        $timezone = new \DateTimeZone(wp_timezone_string());
        $time     = new \DateTime($this->getField('end'), $timezone);

        return $format ? $time->format($format) : $time;
    }

    /**
     * Get time from until
     *
     * @return string
     */
    public function getTimeFromUntil()
    {
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');

        $start_date = $this->getStartTime($date_format);
        $end_date   = $this->getEndTime($date_format);

        if ($start_date == $end_date) {
            return sprintf(
                '%1$s from %2$s until %3$s',
                $start_date,
                $this->getStartTime($time_format),
                $this->getEndTime($time_format)
            );
        }

        return sprintf(
            'from %1$s until %2$s',
            $this->getStartTime("$date_format $time_format"),
            $this->getEndTime("$date_format $time_format")
        );
    }

    /**
     * Is over
     *
     * @return bool
     */
    public function isOver()
    {
        return $this->getEndTime('U') < date_i18n('U');
    }

    /**
     * Is private
     *
     * @return bool
     */
    public function isPrivate()
    {
        return $this->getField('private') ? true : false;
    }
}
