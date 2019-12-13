<?php


namespace airBnB\System\Util;


use DateInterval;

abstract class DateManager
{
    public static function diffInvertDateFormat( string $start_date, string $end_date, string $old_glue ): DateInterval
    {
        $new_start_date = self::invertDateFormat( $start_date, $old_glue, '-' );
        $new_end_date = self::invertDateFormat( $end_date, $old_glue, '-' );

        $new_start_date = date_create($new_start_date);

        $new_end_date = date_create($new_end_date);

        return date_diff( $new_start_date, $new_end_date );
    }

    public static function invertDateFormat( string $date, string $old_glue, string $new_glue ): string
    {
        $explode_date = explode( $old_glue, $date );
        $reverse_date = array_reverse( $explode_date );
        $new_date = implode( $new_glue, $reverse_date );

        return $new_date;
    }

    private function __construct() {}
}