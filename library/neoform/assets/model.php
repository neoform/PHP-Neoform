<?php

    namespace neoform\assets;

    use neoform;

    /**
     * Class model
     * @package neoform\assets
     */
    class model {

        protected $enabled;
        protected $map;
        protected $types;

        /**
         * Constructor
         *
         * @param array $config
         */
        public function __construct(array $config) {

            // If enabled, load the asset map
            if ($this->enabled = (bool) $config['enabled']) {
                $this->map = dao::get();
            }

            $this->types = $config['types'];
        }

        /**
         * Get the URL of a CSS file
         *
         * @param string $type
         * @param array  $args
         *
         * @return string
         */
        public function __call($type, $args) {
            if (!empty($args[0])) {
                return $this->enabled && isset($this->map[$type][$args[0]]) ? $this->map[$type][$args[0]] : "{$this->types[$type]['url']}/{$args[0]}";
            }
        }
    }