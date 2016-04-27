#!/usr/bin/env php
<?php

    $opts = getopt('', [ 'env:' ]);
    $env  = trim(isset($opts['env']) ? $opts['env'] : '');

    if (! $env) {
        echo "\033[1;31mERROR\033[0m --env {env_name} must be set\n";
        die;
    }

    $root = realpath(__DIR__ . '/..');
    require_once("{$root}/library/Neoform/Core.php");
    $core = Neoform\Core::build($root, "MyApp\\Environment\\{$env}");

    class PostDeploy extends Neoform\Cli\Model {

        public function init() {

            // Compile Configs
            echo "Compiling configs\t";

            try {
                $this->core->getEnvironment()->buildCache();
            } catch (\Exception $e) {
                echo self::colorText("[FAILED] - {$e->getMessage()}", 'red') . "\n";
            }

            echo self::colorText('[GOOD]', 'green') . "\n";

            // Compile Assets
            echo "Compiling Assets\t";
            if (Neoform\Assets\Config::get()->isEnabled()) {
                try {
                    Neoform\Assets\Lib::compile();
                    echo self::colorText('[GOOD]', 'green') . "\n";
                } catch (\Exception $e) {
                    echo self::colorText("[FAILED] - {$e->getMessage()}", 'red') . "\n";
                }
            } else {
                echo self::colorText("[SKIPPED] - Not Enabled", 'yellow') . "\n";
            }

            echo "------------------\nDone\n";
        }
    }

    new PostDeploy($core, $argv);
