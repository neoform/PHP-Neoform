<?php

    class sql_lib {

        public static function in_marks($count) {
            return $count > 0 ? join(',', array_fill(0, $count, '?')) : '';
        }

        // PHP equivalent of MySQL's INET_ATON()
        public static function ip2int($ip) {
               return sprintf("%u", ip2long($ip));
        }

        // PHP equivalent of MySQL's INET_NTOA()
        public static function int2ip($int) {
            return long2ip(- (4294967295 - ($int - 1)));
        }

    }