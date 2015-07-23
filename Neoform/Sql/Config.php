<?php

    namespace Neoform\Sql;

    use Neoform;

    class Config extends Neoform\Config\Model {

        /**
         * SQL charset (encoding)
         *
         * @return string
         */
        public function getEncoding() {
            return $this->values['encoding'];
        }

        /**
         * The connection name that is use when all else fails to
         *
         * @return string
         */
        public function getDefaultPoolRead() {
            return $this->values['default_pool_read'];
        }

        /**
         * The connection name that is use when all else fails to
         *
         * @return string
         */
        public function getDefaultPoolWrite() {
            return $this->values['default_pool_write'];
        }

        /**
         * @return array
         */
        public function getPools() {
            return $this->values['pools'];
        }
    }