#!/usr/bin/env php
<?php

    namespace neoform;

    $root = realpath(__DIR__ . '/..');
    require_once("{$root}/library/neoform/core.php");
    core::init([
        'extension'   => 'php',
        'environment' => 'sling',

        'application' => "{$root}/application/",
        'external'    => "{$root}/external/",
        'logs'        => "{$root}/logs/",
        'website'     => "{$root}/www/",
    ]);

    class regenerate_config extends cli\model {

        public function init() {
            config\dao::set(
                $this->opt('file'),
                $this->opt('env')
            );
        }
    }

    new regenerate_config('', ['env:', 'file::', ]);

