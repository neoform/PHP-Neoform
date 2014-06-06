<?php

    namespace neoform\type\arr;

    class lib {

        /**
         * Returns a pretty string seperating the array values. [1, 2, 3, 4] becomes "1, 2, 3 and 4"
         *
         * @param array $arr
         *
         * @return mixed|string
         */
        public static function imploder(array $arr) {
            $count = count($arr);
            if ($count === 0) {
                return '';
            } else if ($count === 1) {
                return reset($arr);
            } else {
                $last = $arr[$count - 1];
                unset($arr[$count - 1]);
                return join(', ', $arr) . " and {$last}";
            }
        }

        /**
         * Crunch an array down - removing all fields that are equal to null
         *
         * @param array $arr
         * @param bool  $strict
         *
         * @return array
         */
        public static function collapse(array $arr, $strict=true) {
            $return = [];
            foreach ($arr as $k => $v) {
                if (($strict && $v !== null) || (! $strict && $v != null)) {
                    if (is_array($arr[$k])) {
                        $arr[$k] = self::collapse($arr[$k]);
                        if ($arr[$k]) {
                            $return[$k] = $arr[$k];
                        }
                    } else {
                        $return[$k] = $arr[$k];
                    }
                }
            }
            return $return;
        }

        /**
         * A strict version of array_diff_assoc()
         *
         * @param $arr1
         * @param $arr2
         *
         * @return array 
         */
        function array_diff_assoc_strict($arr1, $arr2) {
            $diff = [];

            // arr1
            foreach ($arr1 as $k => $v) {
                // Common Keys between the two arrays
                if (array_key_exists($k, $arr2)) {
                    // vals are not the same
                    if ($v !== $arr2[$k]) {
                        $diff[$k] = $v;
                    }
                // Key/Val from arr1 doesn't exist in arr2
                } else {
                    $diff[$k] = $v;
                }
            }

            return $diff;
        }
    }
