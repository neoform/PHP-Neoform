<?php

    namespace Neoform\Memcache;

    use Neoform;

    class Config extends Neoform\Config\Model {

        /**
         * Leave blank (empty string) if no prefix is needed this prefix is useful if you have multiple instances of
         * the same code on the same memcache pool (maybe prod/dev on one memcache pool)
         *
         * @return string
         */
        public function getKeyPrefix() {
            return $this->values['key_prefix'];
        }

        /**
         * @return string
         */
        public function getDefaultPool() {
            return $this->values['default_pool'];
        }

        /**
         * @return array
         */
        public function getPools() {
            return $this->values['pools'];
        }
    }