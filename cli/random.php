#!/usr/bin/env php
<?php

    $opts = getopt('', [ 'env:' ]);
    $env  = trim(strtolower(isset($opts['env']) ? $opts['env'] : ''));

    if (! $env) {
        echo "\033[1;31mERROR\033[0m --env {env_name} must be set\n";
        die;
    }

    $root = realpath(__DIR__ . '/..');
    require_once("{$root}/library/neoform/core.php");
    neoform\core::init([
        'extension'   => 'php',
        'environment' => $env,

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

