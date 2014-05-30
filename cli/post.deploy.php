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

    class post_deploy extends neoform\cli\model {

        public function init() {

            // Compile Configs
            echo "Compiling configs\t";
            neoform\config::instance();
            echo self::color_text('[GOOD]', 'green') . "\n";

            // Compile Assets
            echo "Compiling assets\t";
            neoform\assets\lib::compile();
            echo self::color_text('[GOOD]', 'green') . "\n";

            echo "\ndone\n";
        }
    }

    new post_deploy;
