<?php

    namespace Neoform\Locale;

    use Neoform;

    class Instance {

        /**
         * @var bool is the locale translation system active
         */
        protected $active = false;

        /**
         * @var array all iso2's allowed
         */
        protected $allIso2;

        /**
         * @var string locale iso2
         */
        protected $defaultIso2;

        /**
         * @var string locale iso2
         */
        protected $currentIso2;

        /**
         * @var int
         */
        protected $namespaceId;

        /**
         * @var int[] id translation (name to id)
         */
        protected $namespaceIds = [];

        /**
         * @var array translations
         */
        protected $messages;

        /**
         * @var string base URL used to identify this locale
         */
        protected $slug;

        /**
         * @var array translated URL structs
         */
        protected $routes;

        /**
         * @param Config $config
         */
        public function __construct(Neoform\Locale\Config $config) {
            $this->active      = (bool) $config->isActive();
            $this->allIso2     = array_merge([], $config->getAllowed());
            $this->defaultIso2 = (string) $config->getDefault();
            $this->set((string) $config->getDefault());
        }

        /**
         * Get current locale iso2
         *
         * @return mixed
         */
        public function get() {
            return $this->currentIso2;
        }

        /**
         * Get default locale iso2
         *
         * @return mixed
         */
        public function getDefault() {
            return $this->defaultIso2;
        }

        /**
         * All available locales
         *
         * @return array
         */
        public function all() {
            return $this->allIso2;
        }

        /**
         * Set the current locale
         *
         * @param $iso2
         */
        public function set($iso2) {
            $this->currentIso2 = $iso2;
            $this->slug        = $iso2 === $this->defaultIso2 ? '' : "/{$iso2}";
        }

        /**
         * Set the current namespace by name
         *
         * @param string $namespaceName
         */
        public function setNamespace($namespaceName) {

            if (! $this->active) {
                return;
            }

            if (! isset($this->namespaceIds[$namespaceName])) {
                $this->namespaceIds[$namespaceName] = (int) current(Neoform\Locale\Nspace\Dao::get()->by_name(
                    $namespaceName
                ));
            }

            $this->namespaceId = $this->namespaceIds[$namespaceName];

            if (! isset($this->messages[$this->namespaceId])) {
                $this->_loadTranslations($this->namespaceId);
            }
        }

        /**
         * Load/set the route translations
         *
         * @param array $routes
         */
        public function setRoutes(array $routes) {
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
            if (! $this->active || $this->defaultIso2 === $this->currentIso2) {
                return $key;
            }

            $crc32 = crc32($key); // crc32 makes the keys in the array smaller (saves on memory)

            if ($namespace_name === null) {
                if ($this->namespaceId && isset($this->messages[$this->namespaceId][$crc32])) {
                    return $this->messages[$this->namespaceId][$crc32];
                }
            } else {

                // Look up the namespace id by name - incase we don't know it yet
                if (isset($this->namespaceIds[$namespace_name])) {
                    $namespace_id = $this->namespaceIds[$namespace_name];
                } else {
                    $namespace_id = $this->namespaceIds[$namespace_name] = (int) current(Neoform\Locale\Nspace\Dao::get()->by_name(
                        $namespace_name
                    ));
                }

                // Check if we have this translation dictionary loaded
                if (! isset($this->messages[$namespace_id])) {
                    $this->_loadTranslations($namespace_id);
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
            return ($this->slug) . (isset($this->routes[$route]) ? $this->routes[$route] : $route);
        }

        /**
         * Load translations
         *
         * @param integer $namespace_id
         */
        protected function _loadTranslations($namespace_id) {
            if ($this->currentIso2) {
                $this->messages[$namespace_id] = Lib::byLocaleNamespace(
                    $this->currentIso2,
                    $namespace_id
                );
            }
        }
    }