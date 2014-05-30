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

    if (! in_array(neoform\cli\model::get_user(), [ 'www-data', 'root', ])) {
        echo neoform\cli\model::color_text("ERROR", 'red', true) . " - This script must be run as www-data or root.\n";
        die;
    }

    $table   = isset($argv[1]) ? $argv[1] : null;
    $entity  = isset($argv[2]) ? $argv[2] : null;
    $options = array_splice($argv, 3);

    if (! $table) {
        throw new \exception('generate.php TABLE ENTITY_TYPE');
    }

    $table_info = neoform\sql\parser::get_table($table);

    switch ($entity) {
        case 'dao':
        case 'collection':
        case 'exception':
        case 'definition':
        case 'api':
        case 'lib':
        case 'model':
            $class = '\\neoform\\entity\\generate\\' . $table_info->table_type() . "\\{$entity}";
            $code  = new $class($table_info, $options);

            if (in_array('--install', $options)) {
                $path = neoform\core::path('application') . '/neoform/' . str_replace('_', '/', "{$table}_{$entity}.php");
            } else if (in_array('--installsys', $options)) {
                $path = neoform\core::path('library') . '/neoform/' . str_replace('_', '/', "{$table}_{$entity}.php");
            } else {
                $path = __DIR__ . '/' . str_replace('_', '/', "{$table}_{$entity}.php");
            }

            if (! neoform\disk\lib::file_put_contents($path, str_replace("\t", "    ", $code->get_code()))) {
                echo neoform\cli\model::color_text("ERROR", 'red', true) . " WRITING TO {$path}\n";
            }

            echo "{$path} [" . neoform\cli\model::color_text("OK", 'green') . "]\n";

            break;

        case 'all':
            foreach (['definition', 'dao', 'model', 'collection', 'lib', 'api', 'exception'] as $entity) {
                $class = '\\neoform\\entity\\generate\\' . $table_info->table_type() . "\\{$entity}";
                $code  = new $class($table_info, $options);

                if (in_array('--install', $options)) {
                    $path = neoform\core::path('application') . '/neoform/' . str_replace('_', '/', "{$table}_{$entity}.php");
                } else if (in_array('--installsys', $options)) {
                    $path = neoform\core::path('library') . '/neoform/' . str_replace('_', '/', "{$table}_{$entity}.php");
                } else {
                    $path = __DIR__ . '/' . str_replace('_', '/', "{$table}_{$entity}.php");
                }

                if (! neoform\disk\lib::file_put_contents($path, str_replace("\t", "    ", $code->get_code()))) {
                    echo neoform\cli\model::color_text("ERROR", 'red', true) . " WRITING TO {$path}\n";
                }

                echo $path . " [" . neoform\cli\model::color_text("OK", 'green') . "]\n";
            }

            break;

        default:
            throw new \exception('generate.php TABLE ENTITY_TYPE');
    }

    echo "\n\n";






