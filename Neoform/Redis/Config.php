<?php

    namespace Neoform\Redis;

    use Neoform;

    class Config extends Neoform\Config\Model {

        /**
         * @return string
         */
        public function getKeyPrefix() {
            return $this->values['key_prefix'];
        }

        /**
         * @return string
         */
        public function getDefaultPoolRead() {
            return $this->values['default_pool_read'];
        }

        /**
         * @return string
         */
        public function getDefaultPoolWrite() {
            return $this->values['default_pool_write'];
        }

        /**
         * @return bool
         */
        public function isPersistentConnection() {
            return (bool) $this->values['persistent_connection'];
        }

        /**
         * @return int
         */
        public function getPersistentConnectionTtl() {
            return (int) $this->values['persistent_connection_timeout'];
        }

        /**
         * @return string
         */
        public function getPersistentConnectionId() {
            return $this->values['persistent_connection_id'];
        }

        /**
         * @return array
         */
        public function getPools() {
            return $this->values['pools'];
        }
    }