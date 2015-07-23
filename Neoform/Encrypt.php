<?php

    namespace Neoform;

    class Encrypt extends Service\Singleton {

        /**
         * @var Encrypt\Model
         */
        protected $encrypt;

        /**
         * @param string $connectionPool
         */
        public function __construct($connectionPool) {

        }

        /**
         * @return Encrypt\Model
         */
        public function get() {
            if (! $this->encrypt) {
                $this->open();
            }

            return $this->encrypt;
        }

        /**
         * @return $this
         * Create encryption model
         */
        public function open() {
            $config = Encrypt\Config::get();
            $this->encrypt = new Encrypt\Model(
                $config->getMode(),
                $config->getCipher()
            );
            return $this;
        }

        /**
         * @return $this
         */
        public function close() {
            $this->encrypt = null;
            return $this;
        }
    }