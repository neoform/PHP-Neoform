<?php

    namespace neoform\assets;

    abstract class processor {

        protected $content;
        protected $config;

        final public function __construct(array $config) {
            $this->config = $config;
            $this->init();
        }

        abstract protected function init();

        /**
         * Is executed on each asset
         */
        abstract public function compile();

        /**
         * @param string $content
         */
        final public function set_content($content) {
            $this->content = $content;
        }

        /**
         * @return string
         */
        final public function get_content() {
            return $this->content;
        }
    }