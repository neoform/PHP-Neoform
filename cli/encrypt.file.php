#!/usr/bin/env php
<?php

    $root = realpath(__DIR__ . '/..');
    require_once($root . '/library/core.php');
    core::init([
        'extension'   => 'php',
        'environment' => 'sling',

        'application' => $root . '/application/',
        'library'     => $root . '/library/',
        'external'    => $root . '/external/',
        'entities'    => $root . '/entities/',
        'logs'        => $root . '/logs/',
        'website'     => $root . '/www/',
    ]);

    class encrypt_file extends cli_model {

        const ID_STR = '#AAABBBCCCDDD000111222333444555666777888999000#';

        public function init() {
            global $argv;

            if (! isset($argv[1]) || ! realpath($argv[1]) || ! file_exists(realpath($argv[1]))) {
                echo self::color_text('Please specify a valid file', 'red', true) . "\n";
                return;
            }

            $filepath = realpath($argv[1]);

            echo 'Enter password:';
            $password = self::readpassword();

            if (filesize($filepath) > 10000000) {
                echo self::color_text('File size larger than 10MB, this tool isn\'t designed for large files', 'red', true) . "\n";
                die;
            }

            $file_contents = file_get_contents($filepath);

            if (substr($filepath, -6) === '.crypt') {
                $decrypted_string = encrypt_lib::decrypt(MCRYPT_RIJNDAEL_256, $password, $file_contents);

                if (substr($decrypted_string, 0, strlen(self::ID_STR)) === self::ID_STR) {
                    $file_contents = substr($decrypted_string, strlen(self::ID_STR));
                    file_put_contents($filepath, $file_contents);
                    rename($filepath, substr($filepath, 0, -6));
                    echo self::color_text('File decrypted', 'green', true) . "\n";
                } else {
                    echo self::color_text('File failed to be decrypted', 'red', true) . "\n";
                }
            } else {
                $encrypted_string = encrypt_lib::encrypt(MCRYPT_RIJNDAEL_256, $password, self::ID_STR . $file_contents);
                if ($encrypted_string) {
                    file_put_contents($filepath, $encrypted_string);
                    rename($filepath, "{$filepath}.crypt");
                    echo self::color_text('File encrypted', 'green', true) . "\n";
                } else {
                    echo self::color_text('File failed to be encrypted', 'red', true) . "\n";
                }
            }
        }
    }

    new encrypt_file();