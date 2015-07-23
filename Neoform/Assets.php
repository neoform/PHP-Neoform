<?php

    namespace Neoform;

    /**
     * Class assets
     * @package neoform
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
         * @throws Sql\Exception
         */
        public function open() {
            $this->assets = new Assets\Model(Assets\Config::get());
            return $this;
        }

        /**
         * @throws Sql\Exception
         */
        public function close() {
            $this->assets = null;
            return $this;
        }
    }
