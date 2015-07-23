<?php

    namespace Neoform\Config;

    use Neoform;

    /**
     *  Config DAO
     */
    class Dao {

        /**
         * @var string
         */
        protected $cachePath;

        /**
         * Construct
         *
         * @param string $cachePath
         */
        public function __construct($cachePath) {
            $this->cachePath = $cachePath;
        }

        /**
         * Load a config model
         *
         * @param string $configName
         *
         * @return Neoform\Config\Model
         * @throws Exception
         */
        public function get($configName) {
            try {
                return include($this->compiledFilePath($configName));
            } catch (\Exception $e) {
                throw new Exception('Config cache "' . $configName . '" not found', 0, $e);
            }
        }

        /**
         * Compile config collection into cache files
         *
         * @param Collection $configCollection
         *
         * @return Collection
         * @throws Exception
         */
        public function set(Collection $configCollection) {
            foreach ($configCollection as $key => $configModel) {
                $code = '<'.'?'.'php'.
                        "\n\n// DO NOT MODIFY THIS FILE DIRECTLY, THIS IS A CACHE FILE AND WILL BE OVERWRITTEN AUTOMATICALLY.\n\n".
                        'return new ' . get_class($configModel) . '(' . var_export($configModel->toArray(), true) . ");\n\n";

                if (! Neoform\Disk\Lib::file_put_contents($this->compiledFilePath($key), $code)) {
                    throw new Neoform\Config\Exception('Could not write to the compiled config file to disk.');
                }
            }

            return $configCollection;
        }

        /**
         * Delete a config cache file
         *
         * @param string $file
         */
        public function del($file) {
            unlink($this->compiledFilePath($file));
        }

        /**
         * The file path where the compiled configs are stored
         *
         * @param string $file
         *
         * @return string
         */
        protected function compiledFilePath($file) {
            $file = str_replace('\\', '/', $file);
            return "{$this->cachePath}/config/{$file}." . EXT;
        }
    }