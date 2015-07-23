<?php

    namespace Neoform\Encrypt;

    class Lib {

        /**
         * Random string generator. Encryption grade.
         *
         * @param int $length
         *
         * @return string random binary data
         */
        public static function rand($length) {
            do {
                $rand = openssl_random_pseudo_bytes($length, $good);
            } while (! $good);

            return $rand;
        }

        /**
         * Get a very random number
         *
         * @param integer $min
         * @param integer $max
         *
         * @return integer
         */
        public static function num($min, $max) {
            $range = $max - $min + 1;

            do {
                $result = floor($range * (hexdec(bin2hex(openssl_random_pseudo_bytes(4))) / 0xffffffff));
            } while ($result == $range);

            return (int) ($result + $min);
        }
    }
