<?php

    namespace neoform\assets;

    use neoform;

    class dao {

        protected $config;

        /**
         * Directory where config cache files are stored
         */
        const ASSETS_MAP_DIR = '/cache/assets';

        public function __construct() {
            $this->config = neoform\config::instance()['assets'];
        }

        /**
         * Load an assets map
         *
         * @return array
         * @throws \exception
         */
        public static function get() {
            try {
                return include(neoform\core::path('application') . self::ASSETS_MAP_DIR . '/' . neoform\core::environment() . '.' . EXT);
            } catch (\exception $e) {
                throw new \exception('Assets not compiled');
            }
        }

        /**
         * Load config from source and compile into cache file
         *
         * @throws \exception
         */
        public function compile() {

            if (! $this->config['enabled']) {
                throw new \exception('Compiled assets not enabled for this environment');
            }

            $map = [];

            foreach ($this->config['types'] as $type => $details) {
                $map[$type] = [];

                if (empty($this->config['types'][$type]['processor'])) {
                    $processor = null;
                } else {
                    $processor = new $this->config['types'][$type]['processor']($this->config);
                }

                foreach (glob("{$details['path']}/*.{$type}") as $path) {
                    if (preg_match('`([^/]+)\.' . quotemeta($type) . '`i', $path, $match)) {
                        $file_name = self::compile_asset(
                            $path,
                            $details['path'],
                            $match[1],
                            $type,
                            $processor
                        );

                        $map[$type]["{$match[1]}.{$type}"] = "{$details['url']}/{$file_name}";
                    }
                }
            }

            $code = '<'.'?'.'php'.
                "\n\n// DO NOT MODIFY THIS FILE DIRECTLY, IT IS A CACHE FILE AND GETS OVERWRITTEN AUTOMATICALLY.\n\n".
                'return ' . var_export($map, true) . ";\n\n";

            if (! neoform\disk\lib::file_put_contents(neoform\core::path('application') . self::ASSETS_MAP_DIR . '/' . neoform\core::environment() . '.' . EXT, $code)) {
                throw new \exception('Could not write to the assets map file.');
            }
        }

        /**
         * Delete map file
         *
         * @param string $file
         */
        public function del() {
            unlink(neoform\core::path('application') . self::ASSETS_MAP_DIR . '/' . neoform\core::environment() . '.' . EXT);
        }

        /**
         * Compile an asset file and store it in a uniquely named file
         *
         * @param string $src_path
         * @param string $dst_dir
         * @param string $name
         * @param string $type
         * @param processor|null $processor
         *
         * @return string file name
         */
        protected function compile_asset($src_path, $dst_dir, $name, $type, $processor=null) {
            $content   = file_get_contents($src_path);
            $file_name = substr(str_replace(['/', '=', '+', ], '', base64_encode(md5($content, 1))), 0, 6) . ".{$type}";

            // Search and replace in the CSS content
            if (!empty($this->config['types'][$type]['processor'])) {
                $processor->set_content($content);
                $processor->compile();
                $content = $processor->get_content();
            }

            // Save asset file to uniquely named file
            file_put_contents("{$dst_dir}/{$file_name}", $content);

            return $file_name;
        }
    }