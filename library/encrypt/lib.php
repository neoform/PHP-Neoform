<?php

    class encrypt_lib {

        /**
         * Get the max allowed length of the cipher's key
         *
         * @param string $cipher
         *
         * @return int
         */
        public static function max_key_length($cipher) {
            return mcrypt_get_key_size($cipher, MCRYPT_MODE_CBC);
        }

        /**
         * Encrypt a string with an AES cipher in CBC mode
         *
         * @param string $cipher - MCRYPT_RIJNDAEL_256 is a good choice
         * @param string $key if the key is longer than the cipher can handle, it is truncated to max allowable size.
         * @param string $plaintext
         *
         * @return string
         */
        public static function encrypt($cipher, $key, $plaintext)
        {
            $max_key_len = mcrypt_get_key_size($cipher, MCRYPT_MODE_CBC);

            if (strlen($key) > $max_key_len) {
                $key = substr($key, 0, $max_key_len);
            }

            $iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher, MCRYPT_MODE_CBC), MCRYPT_RAND);

            // IV (initialization vector) is not a secret, but it is necessary to give a CBC string the entropy it needs
            return $iv . mcrypt_encrypt(
                $cipher,
                $key,
                $plaintext,
                MCRYPT_MODE_CBC,
                $iv
            );
        }

        /**
         * Decrypt a string with an AES cipher in CBC mode
         *
         * @param string $cipher
         * @param string $key if the key is longer than the cipher can handle, it is truncated to max allowable size.
         * @param string $encrypted_str
         *
         * @return string
         */
        public static function decrypt($cipher, $key, $encrypted_str)
        {
            $max_key_len = mcrypt_get_key_size($cipher, MCRYPT_MODE_CBC);

            if (strlen($key) > $max_key_len) {
                $key = substr($key, 0, $max_key_len);
            }

            $iv_length = mcrypt_get_iv_size($cipher, MCRYPT_MODE_CBC);
            $iv        = substr($encrypted_str, 0, $iv_length);

            return mcrypt_decrypt(
                $cipher,
                $key,
                substr($encrypted_str, $iv_length),
                MCRYPT_MODE_CBC,
                $iv
            );
        }

        /**
         * Random string generator. Encryption grade.
         *
         * @param int $length
         *
         * @return string random binary data
         */
        public static function rand($length) {
            do
            {
                $rand = openssl_random_pseudo_bytes($length, $good);
            } while (! $good);

            return $rand;
        }
    }