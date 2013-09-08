<?php

    namespace neoform;

    class encrypt_lib {

        /**
         * Get the max allowed length of the cipher's key
         *
         * @param string $cipher
         *
         * @return int
         */
        public static function max_key_length($cipher) {
            return \mcrypt_get_key_size($cipher, MCRYPT_MODE_CBC);
        }

        /**
         * Encrypt a string with an AES cipher in CBC mode
         * The encryption cipher and mode are prepended to the encrypted output. If the encryption method is changed
         * at a later date, there will be no issues decrypting since the decrypt function uses those values.
         *
         * @param string $key if the key is longer than the cipher can handle, it is truncated to max allowable size.
         * @param string $plaintext
         *
         * @return string
         */
        public static function encrypt($key, $plaintext) {
            $max_key_len = \mcrypt_get_key_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);

            if (\strlen($key) > $max_key_len) {
                $key = \substr($key, 0, $max_key_len);
            }

            $iv     = \mcrypt_create_iv(\mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_RAND);
            $prefix = \str_pad(MCRYPT_RIJNDAEL_256 . ':' . MCRYPT_MODE_CBC, 128, "\x00");

            // IV (initialization vector) is not a secret, but it is necessary to give a CBC string the entropy it needs
            return "{$prefix}{$iv}" . \mcrypt_encrypt(
                MCRYPT_RIJNDAEL_256,
                $key,
                $prefix . $plaintext,
                MCRYPT_MODE_CBC,
                $iv
            );
        }

        /**
         * Decrypt a string that was encrypted using the encrypt function above
         *
         * @param string $key if the key is longer than the cipher can handle, it is truncated to max allowable size.
         * @param string $encrypted_str
         *
         * @return string|null on error
         */
        public static function decrypt($key, $encrypted_str) {
            $prefix = \substr($encrypted_str, 0, 128);
            $header = \explode(':', $prefix);

            if (\count($header) !== 2) {
                return;
            }

            $cipher        = \trim($header[0]);
            $mode          = \trim($header[1]);
            $encrypted_str = \substr($encrypted_str, 128);
            $max_key_len   = \mcrypt_get_key_size($cipher, $mode);

            if (\strlen($key) > $max_key_len) {
                $key = \substr($key, 0, $max_key_len);
            }

            $iv_length = \mcrypt_get_iv_size($cipher, $mode);
            $iv        = \substr($encrypted_str, 0, $iv_length);

            $unencrypted_string =  \mcrypt_decrypt(
                $cipher,
                $key,
                \substr($encrypted_str, $iv_length),
                $mode,
                $iv
            );

            return \substr($unencrypted_string, 0, 128) === $prefix ? \substr($unencrypted_string, 128) : null;
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
                $rand = \openssl_random_pseudo_bytes($length, $good);
            } while (! $good);

            return $rand;
        }
    }
