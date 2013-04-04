#!/usr/bin/env php
<?php

    $root = realpath(__DIR__ . '/..');
    require_once($root . '/library/core.php');
    core::init([
        'extension'   => 'php',
        'environment' => 'sample',

        'application' => $root . '/application/',
        'library'     => $root . '/library/',
        'external'    => $root . '/external/',
        'entities'    => $root . '/entities/',
        'logs'        => $root . '/logs/',
        'website'     => $root . '/www/',
    ]);

    if (! in_array(cli_model::get_user(), [ 'www-data', 'root', ])) {
        echo cli_model::color_text("ERROR", 'red', true) . " - This script must be run as www-data or root.\n";
        die;
    }

    try {
        $table   = isset($argv[1]) ? $argv[1] : null;
        $entity  = isset($argv[2]) ? $argv[2] : null;
        $options = array_splice($argv, 3);

        if (! $table) {
            throw new exception('generate.php TABLE ENTITY_TYPE');
        }

        $table_info = sql_parser::get_table($table);

        switch ($entity) {
            case 'dao':
            case 'collection':
            case 'exception':
            case 'definition':
            case 'api':
            case 'lib':
            case 'model':
                $class = 'generate_' . $table_info->table_type() . '_' . $entity;
                $code = new $class($table_info, $options);

                $path = __DIR__ . '/' . str_replace('_', '/', $table . '_' . $entity . '.php');

                if (! disk_lib::file_put_contents($path, str_replace("\t", "    ", $code->get_code()))) {
                    echo cli_model::color_text("ERROR", 'red', true) . ' WRITING TO ' . $path . "\n";
                }

                echo $path . " [" . cli_model::color_text("OK", 'green') . "]\n";

                break;

            case 'all':
                foreach (['definition', 'dao', 'model', 'collection', 'lib', 'api', 'exception'] as $entity) {
                    $class = 'generate_' . $table_info->table_type() . '_' . $entity;
                    $code = new $class($table_info, $options);

                    $path = __DIR__ . '/' . str_replace('_', '/', $table . '_' . $entity . '.php');

                    if (! disk_lib::file_put_contents($path, str_replace("\t", "    ", $code->get_code()))) {
                        echo cli_model::color_text("ERROR", 'red', true) . ' WRITING TO ' . $path . "\n";
                    }

                    echo $path . " [" . cli_model::color_text("OK", 'green') . "]\n";
                }

                break;

            default:
                throw new exception('generate.php TABLE ENTITY_TYPE');
        }

        echo "\n\n";
    } catch (exception $e) {
        echo $e->getMessage() . "\n";
    }






