<?php

    class locale_instance {

        use core_instance;

        /**
         * @var bool is the locale translation system active
         */
        protected $active = false;

        /**
         * @var array all iso2's allowed
         */
        protected $all_iso2;

        /**
         * @var string locale iso2
         */
        protected $default_iso2;

        /**
         * @var string locale iso2
         */
        protected $current_iso2;

        /**
         * @var integer
         */
        protected $namespace_id;

        /**
         * @var array id translation (name to id)
         */
        protected $namespace_ids = [];

        /**
         * @var array translations
         */
        protected $messages;

        /**
         * @var string base URL used to identify this locale
         */
        protected $struct;

        /**
         * @var array translated URL structs
         */
        protected $routes;

        /**
         * @param array $config
         */
        public function __construct(array $config) {
            $this->active       = (bool) $config['active'];
            $this->all_iso2     = array_merge([], $config['allowed']);
            $this->default_iso2 = (string) $config['default'];
            $this->set((string) $config['default']);
        }

        /**
         * Get current locale iso2
         *
         * @return mixed
         */
        public function get() {
            return $this->current_iso2;
        }

        /**
         * Get default locale iso2
         *
         * @return mixed
         */
        public function get_default() {
            return $this->default_iso2;
        }

        /**
         * All available locales
         *
         * @return array
         */
        public function all() {
            return $this->all_iso2;
        }

        /**
         * Set the current locale
         *
         * @param $iso2
         */
        public function set($iso2) {

            if (! $this->active) {
                return;
            }

            $this->current_iso2 = $iso2;
            $this->struct       = $iso2 === $this->default_iso2 ? '' : "/{$iso2}";
        }

        /**
         * Set the current namespace by name
         *
         * @param string $namespace_name
         */
        public function set_namespace($namespace_name) {

            if (! $this->active) {
                return;
            }

            if (! isset($this->namespace_ids[$namespace_name])) {
                $this->namespace_ids[$namespace_name] = (int) current(entity::dao('locale_namespace')->by_name(
                    $namespace_name
                ));
            }

            $this->namespace_id = $this->namespace_ids[$namespace_name];

            if (! isset($this->messages[$this->namespace_id])) {
                $this->_load_translations($this->namespace_id);
            }
        }

        /**
         * Load/set the route translations
         *
         * @param array $routes
         */
        public function set_routes(array $routes) {
            $this->routes = $routes;
        }

        /**
         * Translate a single key with an optional namespace
         *
         * @param string      $key
         * @param string|null $namespace_name
         *
         * @return string
         */
        public function translate($key, $namespace_name=null) {

            // If locale is either not active, or the current locale is the default one, no translation happens
            if (! $this->active || $this->default_iso2 === $this->current_iso2) {
                return $key;
            }

            $crc32 = crc32($key); // crc32 makes the keys in the array smaller (saves on memory)

            if ($namespace_name === null) {
                if ($this->namespace_id && isset($this->messages[$this->namespace_id][$crc32])) {
                    return $this->messages[$this->namespace_id][$crc32];
                }
            } else {

                // Look up the namespace id by name - incase we don't know it yet
                if (isset($this->namespace_ids[$namespace_name])) {
                    $namespace_id = $this->namespace_ids[$namespace_name];
                } else {
                    $namespace_id = $this->namespace_ids[$namespace_name] = (int) current(entity::dao('locale_namespace')->by_name(
                        $namespace_name
                    ));
                }

                // Check if we have this translation dictionary loaded
                if (! isset($this->messages[$namespace_id])) {
                    $this->_load_translations($namespace_id);
                }

                // Return the translation if we have it
                if (isset($this->messages[$namespace_id][$crc32])) {
                    return $this->messages[$namespace_id][$crc32];
                }
            }

            return $key;
        }

        /**
         * Translate a route
         *
         * @param string $route
         *
         * @return string
         */
        public function route($route) {
            return ($this->struct) . (isset($this->routes[$route]) ? $this->routes[$route] : $route);
        }

        /**
         * Load translations
         *
         * @param integer $namespace_id
         */
        protected function _load_translations($namespace_id) {
            if ($this->current_iso2) {
                $this->messages[$namespace_id] = locale_lib::by_locale_namespace(
                    $this->current_iso2,
                    $namespace_id
                );
            }
        }
    }