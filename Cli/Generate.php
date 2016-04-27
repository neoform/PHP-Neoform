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

            if (!in_array(Neoform\Cli\Model::getUser(), [ 'www-data', 'root', ])) {
                echo Neoform\Cli\Model::colorText(
                        "ERROR", 'red', true
                    ) . " - This script must be run as www-data or root.\n";
                die;
            }

            $namespace = $this->getArg(2);
            $table     = $this->getArg(3);
            $entity    = $this->getArg(4);
            $options   = array_slice($this->getArgs(), 5);

            if (!$table) {
                throw new \Exception('generate.php TABLE ENTITY_TYPE');
            }

            $tableInfo = Neoform\Sql\Parser::getTable($table);

            switch ($entity) {
                case 'Dao':
                case 'Collection':
                case 'Exception':
                case 'Details':
                case 'Api':
                case 'Validator\Insert':
                case 'Validator\Update':
                case 'Lib':
                case 'Model':
                    if ($entity === 'Validator\Update' && $tableInfo->tableType() === 'Link') {
                        throw new \Exception('Link entities do not make use of Validator\Update');
                    }

                    $class = "\\Neoform\\Entity\\Generate\\{$tableInfo->tableType()}\\{$entity}";
                    $code  = new $class($namespace, $tableInfo, $options);
                    $path  = str_replace([ ' ', '\\' ], '/', ucwords(str_replace('_', ' ', "{$table}_{$entity}")));

                    if (in_array('--install', $options)) {
                        $path = "{$this->core->getLibraryPath()}/{$namespace}/{$path}.php";
                    } else {
                        $path = __DIR__ . "/{$path}.php";
                    }

                    if (! Neoform\Disk\Lib::file_put_contents($path, str_replace("\t", "    ", $code->getCode()))) {
                        echo Neoform\Cli\Model::colorText("ERROR", 'red', true) . " WRITING TO {$path}\n";
                    }

                    echo "{$path} [" . Neoform\Cli\Model::colorText("OK", 'green') . "]\n";

                    break;

                case 'all':
                    foreach ([
                                 'Dao', 'Model', 'Collection', 'Lib', 'Details', 'Api', 'Validator\Insert',
                                 'Validator\Update', 'Exception'
                             ] as $entity) {
                        if ($entity === 'Validator\Update' && $tableInfo->tableType() === 'Link') {
                            continue;
                        }
                        $class = "\\Neoform\\Entity\\Generate\\{$tableInfo->tableType()}\\{$entity}";
                        $code  = new $class($namespace, $tableInfo, $options);
                        $path  = str_replace([ ' ', '\\' ], '/', ucwords(str_replace('_', ' ', "{$table}_{$entity}")));

                        if (in_array('--install', $options)) {
                            $path = "{$this->core->getLibraryPath()}/{$namespace}/{$path}.php";
                        } else {
                            $path = __DIR__ . "/{$path}.php";
                        }

                        if (!Neoform\Disk\Lib::file_put_contents($path, str_replace("\t", "    ", $code->getCode()))) {
                            echo Neoform\Cli\Model::colorText("ERROR", 'red', true) . " WRITING TO {$path}\n";
                        }

                        echo "{$path} [" . Neoform\Cli\Model::colorText("OK", 'green') . "]\n";
                    }

                    break;

                default:
                    throw new \Exception('generate.php --env=[ENVIRONMENT] [NAMESPACE] [TABLE] [ENTITY_TYPE]');
            }

            echo "\n\n";
        }
    }

    new PostDeploy($core, $argv);