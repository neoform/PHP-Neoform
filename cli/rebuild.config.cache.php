#!/usr/bin/env php
<?php

    $root = realpath(__DIR__ . '/..');
    require_once("{$root}/library/core.php");
    core::init([
        'extension'   => 'php',
        'environment' => 'sling',

        'application' => "{$root}/application/",
        'external'    => "{$root}/external/",
        'logs'        => "{$root}/logs/",
        'website'     => "{$root}/www/",
    ]);

    class regenerate_config extends cli_model {

        public function init() {
            config_dao::set(
                $this->opt('file'),
                $this->opt('env')
            );
        }
    }

    new regenerate_config('', ['env:', 'file::', ]);

