<?php

    namespace neoform;

    class type_array_lib {

        // Returns a pretty string seperating the array values. [1, 2, 3, 4] becomes "1, 2, 3 and 4"
        public static function imploder(array $arr) {
            $count = \count($arr);
            \reset($arr);
            if ($count === 0) {
                return '';
            } else if ($count === 1) {
                return \current($arr);
            } else {
                $last = $arr[$count - 1];
                unset($arr[$count - 1]);
                return \join(', ', $arr) . ' and ' . $last;
            }
        }

        // Crunch an array down - removing all fields that are equal to null
        public static function collapse(array $arr, $strict=true) {
            $return = [];
            foreach ($arr as $k => $v) {
                if (($strict && $v !== null) || (! $strict && $v != null)) {
                    if (\is_array($arr[$k])) {
                        $arr[$k] = self::collapse($arr[$k]);
                        if (\count($arr[$k])) {
                            $return[$k] = $arr[$k];
                        }
                    } else {
                        $return[$k] = $arr[$k];
                    }
                }
            }
            return $return;
        }
    }