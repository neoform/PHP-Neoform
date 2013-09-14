<?php

    namespace neoform\encrypt;

    class lib {

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
    }
