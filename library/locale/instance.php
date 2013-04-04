<?php

    class locale_instance {

        use core_instance;

        protected $current;
        protected $nspace;
        protected $messages;
        protected $struct;
        protected $routes;

        public function get() {
            return $this->current;
        }

        public function all() {
            return array_merge([], core::config()->system['locale']['allowed']);
        }

        public function set($locale) {
            $this->current = $locale;
            $this->struct  = $locale === core::config()->system['locale']['default'] ? '' : '/' . $locale;
            $this->_load_translations();
        }

        public function set_namespace($namespace) {
            $this->nspace = new locale_namespace_model(current(locale_namespace_dao::by_name(
                $namespace
            )));
            $this->_load_translations();
        }

        public function get_namespace() {
            return $this->nspace;
        }

        public function set_routes(array $routes) {
            $this->routes = $routes;
        }

        public function translate($key, $namespace=null) {

            $crc32 = crc32($key); // crc32 makes the keys in the array smaller (saves on memory)

            if ($namespace === null) {
                if ($this->nspace && isset($this->messages[$this->nspace->name][$crc32])) {
                    return $this->messages[$this->nspace->name][$crc32];
                }
            } else {
                $this->_load_translations(); // incase it was not yet loaded (this only adds a bit of overhead)
                if (isset($this->messages[$namespace][$crc32])) {
                    return $this->messages[$namespace][$crc32];
                }
            }

            return $key;
        }

        public function route($route) {
            return $this->struct . (isset($this->routes[$route]) ? $this->routes[$route] : $route);
        }

        protected function _load_translations() {
            // load up the locale translations
            if ($this->nspace && ! isset($this->messages[$this->nspace->name]) && $this->current) {
                $this->messages[$this->nspace->name] = locale_lib::by_locale_namespace(
                    $this->current,
                    $this->nspace
                );
            }
        }
    }