<?php

    namespace Neoform\Assets;

    abstract class Processor {

        /**
         * @var string
         */
        protected $content;

        /**
         * @var Config
         */
        protected $config;

        /**
         * @param Config $config
         */
        final public function __construct(Config $config) {
            $this->config = $config;
            $this->init();
        }

        /**
         * Executed upon load
         */
        abstract protected function init();

        /**
         * Is executed on each asset
         */
        abstract public function compile();

        /**
         * @param string $content
         */
        final public function setContent($content) {
            $this->content = $content;
        }

        /**
         * @return string
         */
        final public function getContent() {
            return $this->content;
        }
    }