<?php

    namespace neoform\sql;

    class lib {

        /**
         * Return a bunch of questionmarks and commas
         *
         * @param integer $count
         *
         * @return string
         */
        public static function in_marks($count) {
            return $count > 0 ? join(',', array_fill(0, $count, '?')) : '';
        }

        /**
         * PHP equivalent of MySQL's INET_ATON()
         *
         * @param string $ip
         *
         * @return string
         */
        public static function ip2int($ip) {
            return sprintf("%u", ip2long($ip));
        }

        /**
         * PHP equivalent of MySQL's INET_NTOA()
         *
         * @param integer $int
         *
         * @return string
         */
        public static function int2ip($int) {
            return long2ip( -(4294967295 - ($int - 1)));
        }
    }