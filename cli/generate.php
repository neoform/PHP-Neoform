#!/usr/bin/env php
<?php

    $root = realpath(__DIR__ . '/..');
    require_once("{$root}/neoform/core.php");
    neoform\core::init([
        'extension'   => 'php',
        'environment' => 'sling',

        'application' => "{$root}/application/",
        'external'    => "{$root}/external/",
        'logs'        => "{$root}/logs/",
        'website'     => "{$root}/www/",
    ]);

    if (! in_array(neoform\cli_model::get_user(), [ 'www-data', 'root', ])) {
        echo neoform\cli_model::color_text("ERROR", 'red', true) . " - This script must be run as www-data or root.\n";
        die;
    }

    $table   = isset($argv[1]) ? $argv[1] : null;
    $entity  = isset($argv[2]) ? $argv[2] : null;
    $options = array_splice($argv, 3);

    if (! $table) {
        throw new exception('generate.php TABLE ENTITY_TYPE');
    }

    $table_info = neoform\sql_parser::get_table($table);

    switch ($entity) {
        case 'dao':
        case 'collection':
        case 'exception':
        case 'definition':
        case 'api':
        case 'lib':
        case 'model':
            $class = '\\neoform\\generate\\' . $table_info->table_type() . "\\{$entity}";
            $code  = new $class($table_info, $options);

            if (in_array('--install', $options)) {
                $path = neoform\core::path('application') . '/neoform/' . str_replace('_', '/', "{$table}_{$entity}.php");
            } else if (in_array('--installsys', $options)) {
                $path = neoform\core::path('library') . '/neoform/' . str_replace('_', '/', "{$table}_{$entity}.php");
            } else {
                $path = __DIR__ . '/' . str_replace('_', '/', $table . '_' . $entity . '.php');
            }

            if (! neoform\disk_lib::file_put_contents($path, str_replace("\t", "    ", $code->get_code()))) {
                echo neoform\cli_model::color_text("ERROR", 'red', true) . ' WRITING TO ' . $path . "\n";
            }

            echo $path . " [" . neoform\cli_model::color_text("OK", 'green') . "]\n";

            break;

        case 'all':
            foreach (['definition', 'dao', 'model', 'collection', 'lib', 'api', 'exception'] as $entity) {
                $class = '\\neoform\\generate\\' . $table_info->table_type() . "\\{$entity}";
                $code  = new $class($table_info, $options);

                if (in_array('--install', $options)) {
                    $path = neoform\core::path('application') . '/neoform/' . str_replace('_', '/', $table . '_' . $entity . '.php');
                } else if (in_array('--installsys', $options)) {
                    $path = neoform\core::path('library') . '/neoform/' . str_replace('_', '/', $table . '_' . $entity . '.php');
                } else {
                    $path = __DIR__ . '/' . str_replace('_', '/', $table . '_' . $entity . '.php');
                }

                if (! neoform\disk_lib::file_put_contents($path, str_replace("\t", "    ", $code->get_code()))) {
                    echo neoform\cli_model::color_text("ERROR", 'red', true) . ' WRITING TO ' . $path . "\n";
                }

                echo $path . " [" . neoform\cli_model::color_text("OK", 'green') . "]\n";
            }

            break;

        default:
            throw new exception('generate.php TABLE ENTITY_TYPE');
    }

    echo "\n\n";






