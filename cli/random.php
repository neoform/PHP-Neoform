#!/usr/bin/env php
<?php

    $root = realpath(__DIR__ . '/..');
    require_once($root . '/library/core.php');
    core::init([
        'extension'   => 'php',
        'environment' => 'sample',

        'application' => "{$root}/application/",
        'external'    => "{$root}/external/",
        'logs'        => "{$root}/logs/",
        'website'     => "{$root}/www/",
    ]);

    class generate_rand extends cli_model {

        public function init() {

        }
    }

    $cli = new generate_rand('', ['length:', ]);

    echo "Very random string:\n\n";
    echo wordwrap(base64_encode(encrypt_lib::rand($cli->opt('length') ?: 100)), 75, "\n", true) . "\n\n";






