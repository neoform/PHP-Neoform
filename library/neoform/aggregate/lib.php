<?php

    namespace neoform;

    /**
     * Aggregation library (used for statistic pages)
     */
    abstract class aggregate_lib {

        /**
         * Average over multiple hourly blocks (spaced out by $interval) $iterations is the number of
         * intervals used (going backwards in time)
         *
         * @param type_date $start
         * @param array     $args
         * @param string    $interval
         * @param int       $iterations
         *
         * @return array
         */
        public static function average(type_date $start, array $args=null, $interval='7 day', $iterations=4) {

            $sums = self::sum($start, $args, $interval, $iterations);

            $keys = \array_keys($sums);
            $average = [];
            foreach ($keys as $key) {
                $average[$key] = $sums[$key] / $iterations;
            }

            return $average;
        }

        /**
         * Creates an start/end date block based on an arbitrary start date
         * (it removes the minutes/seconds from that time)
         *
         * @param type_date $start
         *
         * @return array
         */
        public static function get_hour(type_date $start) {
            $start->setTime((int) $start->format('H'), 0, 0);
            $end = clone $start;
            $end->setTime((int) $end->format('H'), 59, 59);
            return [$start, $end];
        }

        /**
         * Two type_date timestamps 00:00:00 to 23:59:59
         *
         * @param type_date $start
         *
         * @return array (0 => start, 1=> end)
         */
        public static function get_day(type_date $start) {
            $start->setTime(0, 0, 0);
            $end = clone $start;
            $end->setTime(23, 59, 59);
            return [$start, $end];
        }

        /**
         * Adds together multiple blocks of data
         *
         * @param type_date $start
         * @param array     $args           passed to self::get($args)
         * @param string    $interval       default: '1 hour'
         * @param int       $iterations     default: 6
         * @param bool      $subtract_first subtract the interval before getting the data
         *
         * @return array    The resulting data for this block
         */
        public static function sum(type_date $start, array $args=null, $interval='1 hour', $iterations=6, $subtract_first=true) {

            // This function only supports hourly or daily blocks
            $by_hour = \substr($interval, -4) === 'hour';
            $by_day  = \substr($interval, -3) === 'day';

            $sums = [];
            if ($iterations) {
                for ($i=0; $i < $iterations; $i++) {
                    if ($subtract_first) {
                        $start->modify('-' . $interval);
                    }

                    if ($by_hour) {
                        list($start, $end) = self::get_hour($start);
                    } else if ($by_day) {
                        list($start, $end) = self::get_day($start);
                    }

                    $counts = static::get($start, $end, $args);
                    foreach ($counts as $k => $v) {
                        if (isset($sums[$k])) {
                            $sums[$k] += (int) $v;
                        } else {
                            $sums[$k] = (int) $v;
                        }
                    }
                    if (! $subtract_first) {
                        $start->modify('-' . $interval);
                    }
                }
            } else {
                if ($subtract_first) {
                    $start->modify('-' . $interval);
                }

                if ($by_hour) {
                    list($start, $end) = self::get_hour($start);
                } else if ($by_day) {
                    list($start, $end) = self::get_day($start);
                }

                $counts = self::get($start, $end);
                foreach ($counts as $k => $v) {
                    if (isset($sums[$k])) {
                        $sums[$k] += (int) $v;
                    } else {
                        $sums[$k] = (int) $v;
                    }
                }
            }

            return $sums;
        }
    }