<?php

    namespace Neoform\Assets;

    use Neoform;

    class Dao {

        /**
         * @var Neoform\Assets\Config
         */
        protected $config;

        /**
         * Directory where config cache files are stored
         */
        const ASSETS_MAP_DIR = '/assets';

        /**
         * Construct
         */
        public function __construct() {
            $this->config = Neoform\Assets\Config::get();
        }

        /**
         * Load an assets map
         *
         * @return array
         * @throws Exception
         */
        public static function get() {
            try {
                return include(self::cacheFilePath());
            } catch (Exception $e) {
                throw new Exception('Assets not compiled', 0, $e);
            }
        }

        /**
         * Load config from source and compile into cache file
         *
         * @throws Exception
         */
        public function compile() {

            if (! $this->config->isEnabled()) {
                throw new Exception('Compiled assets not enabled for this environment');
            }

            $map = [];

            $types = $this->config->getTypes();

            foreach ($types as $type => $details) {
                $map[$type] = [];
                $processor  = !empty($types[$type]['processor']) ? new $types[$type]['processor']($this->config) : null;

                foreach (glob("{$details['path']}/*.{$type}") as $path) {
                    if (preg_match('`([^/]+)\.' . preg_quote($type) . '`i', $path, $match)) {
                        $fileName = self::compileAsset(
                            $path,
                            $details['path'],
                            $type,
                            $processor
                        );

                        $map[$type]["{$match[1]}.{$type}"] = "{$details['url']}/{$fileName}";
                    }
                }
            }

            $code = '<'.'?'.'php'.
                "\n\n// DO NOT MODIFY THIS FILE DIRECTLY, IT IS A CACHE FILE AND GETS OVERWRITTEN AUTOMATICALLY.\n\n".
                'return ' . var_export($map, true) . ";\n\n";

            if (! Neoform\Disk\Lib::file_put_contents(self::cacheFilePath(), $code)) {
                throw new Exception('Could not write to the assets map file.');
            }
        }

        /**
         * Delete map file
         */
        public function del() {
            unlink(self::cacheFilePath());
        }

        /**
         * Compile an asset file and store it in a uniquely named file
         *
         * @param string $src_path
         * @param string $dst_dir
         * @param string $type
         * @param processor|null $processor
         *
         * @return string file name
         */
        protected function compileAsset($src_path, $dst_dir, $type, $processor=null) {
            $content   = file_get_contents($src_path);
            $file_name = substr(str_replace(['/', '=', '+', ], '', base64_encode(md5($content, 1))), 0, 6) . ".{$type}";

            // Search and replace in the CSS content
            $types = $this->config->getTypes();
            if (!empty($types[$type]['processor'])) {
                $processor->setContent($content);
                $processor->compile();
                $content = $processor->getContent();
            }

            // Save asset file to uniquely named file
            file_put_contents("{$dst_dir}/{$file_name}", $content);

            return $file_name;
        }

        /**
         * @return string
         * @throws Exception
         */
        protected static function cacheFilePath() {
            return Neoform\Core::get()->getCachePath() . self::ASSETS_MAP_DIR . '/map.' . EXT;
        }
    }