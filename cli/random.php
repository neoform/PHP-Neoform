#!/usr/bin/env php
<?php

    $root = realpath(__DIR__ . '/..');
    require_once("{$root}/library/neoform/core.php");
    neoform\core::init([
        'extension'   => 'php',
        'environment' => 'sample',

        'application' => "{$root}/application/",
        'external'    => "{$root}/external/",
        'logs'        => "{$root}/logs/",
        'website'     => "{$root}/www/",
    ]);

    class generate_rand extends neoform\cli\model {

        public function init() {
            echo "Very random string:\n\n";
            echo wordwrap(base64_encode(neoform\encrypt\lib::rand($this->opt('length') ?: 100)), 75, "\n", true) . "\n\n";
        }
    }

    new generate_rand('', ['length:', ]);

