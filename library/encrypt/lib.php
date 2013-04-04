<?php

    class encrypt_lib {

        /**
         * Encrypt a string with an AES cipher in CBC mode
         *
         * @param string $key if the key is longer than the cipher can handle, it is truncated to max allowable size.
         * @param string $plaintext
         *
         * @return string
         */
        public static function encrypt($key, $plaintext)
        {
            $max_key_len = mcrypt_get_key_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);

            if (strlen($key) > $max_key_len) {
                $key = substr($key, 0, $max_key_len);
            }

            $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_RAND);

            // IV (initialization vector) is not a secret, but it is necessary to give a CBC string the entropy it needs
            return $iv . mcrypt_encrypt(
                MCRYPT_RIJNDAEL_256,
                $key,
                $plaintext,
                MCRYPT_MODE_CBC,
                $iv
            );
        }

        /**
         * Decrypt a string with an AES cipher in CBC mode
         *
         * @param string $key if the key is longer than the cipher can handle, it is truncated to max allowable size.
         * @param string $encrypted_str
         *
         * @return string
         */
        public static function decrypt($key, $encrypted_str)
        {
            $max_key_len = mcrypt_get_key_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);

            if (strlen($key) > $max_key_len) {
                $key = substr($key, 0, $max_key_len);
            }

            $iv_length = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
            $iv        = substr($encrypted_str, 0, $iv_length);

            return mcrypt_decrypt(
                MCRYPT_RIJNDAEL_256,
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