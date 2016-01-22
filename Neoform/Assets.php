<?php

    namespace Neoform;

    /**
     * Class assets
     * @package Neoform
     */
    class Assets extends Service\Singleton {

        /**
         * @var Assets\Model
         */
        protected $assets;

        /**
         * @param string $name
         */
        public function __construct($name) {

        }

        /**
         * @return Assets\Model
         */
        public function get() {
            if (! $this->assets) {
                $this->open();
            }

            return $this->assets;
        }

        /**
         * @throws Assets\Exception
         */
        public function open() {
            $this->assets = new Assets\Model(Assets\Config::get());
            return $this;
        }

        /**
         * Close
         */
        public function close() {
            $this->assets = null;
            return $this;
        }
    }
