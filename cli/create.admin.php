#!/usr/bin/env php
<?php

    $root = realpath(__DIR__ . '/..');
    require_once($root . '/library/core.php');

    core::init([
        'extension'   => 'php',
        'environment' => 'sling',

        'application' => "{$root}/application/",
        'external'    => "{$root}/external/",
        'logs'        => "{$root}/logs/",
        'website'     => "{$root}/www/",
    ]);

    class create_admin extends cli_model {

        public function init() {

            do {
                do {
                    echo self::color_text('Please enter an email address:', 'green', true) . "\n";
                    $email = self::readline();

                    try {
                        if (user_api::email_available([ 'email' => $email, ])) {
                            break;
                        }
                    } catch (input_exception $e) {
                        echo self::color_text($e->email, 'red', true) . "\n";
                        continue;
                    }

                    echo self::color_text('That user already exists', 'red', true) . "\n";
                } while (1);

                echo self::color_text('Enter password', 'green', true) . "\n";
                $password1 = self::readline();

                echo self::color_text('Repeat password', 'green', true) . "\n";
                $password2 = self::readline();

                try {
                    $user = user_api::insert([
                        'email'     => $email,
                        'password1' => $password1,
                        'password2' => $password2,
                    ]);

                    break;

                } catch (input_exception $e) {
                    if ($e->errors()) {
                        foreach ($e->errors() as $k => $v) {
                            echo self::color_text("{$k}: {$v}", 'red', true) . "\n";
                        }
                    }
                }

            } while (1);

            $roles = new acl_role_collection(null, entity::dao('acl_role')->all());

            $user_acl_roles = [];
            foreach ($roles as $role) {
                $user_acl_roles[] = [
                    'user_id'     => $user->id,
                    'acl_role_id' => $role->id,
                ];
            }

            entity::dao('user_acl_role')->inserts($user_acl_roles);

            echo self::color_text('Done', 'green', true) . "\n";
        }
    }

    new create_admin;