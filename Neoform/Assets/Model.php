<?php

    namespace Neoform\Assets;

    use Neoform;

    /**
     * class Model
     * @package Neoform\Assets
     */
    class Model {

        /**
         * @var bool
         */
        protected $enabled;

        /**
         * @var array
         */
        protected $map;

        /**
         * @var array
         */
        protected $types;

        /**
         * Constructor
         *
         * @param Config $config
         *
         * @throws Exception
         */
        public function __construct(Config $config) {

            // If enabled, load the asset map
            if ($this->enabled = (bool) $config->isEnabled()) {
                $this->map = Dao::get();
            }

            $this->types = $config->getTypes();
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