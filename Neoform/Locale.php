<?php

    namespace Neoform;

    class Locale extends Service\Singleton {

        /**
         * @var Locale\Instance
         */
        protected $locale;

        /**
         * @param string $connectionPool
         */
        public function __construct($connectionPool) {

        }

        /**
         * @return Locale\Instance
         */
        public function get() {
            if (! $this->locale) {
                $this->open();
            }

            return $this->locale;
        }

        /**
         * @return $this
         * @throws Sql\Exception
         */
        public function open() {
            $this->locale = new Locale\Instance(Locale\Config::get());
            return $this;
        }

        /**
         * @return $this
         */
        public function close() {
            $this->locale = null;
            return $this;
        }
    }