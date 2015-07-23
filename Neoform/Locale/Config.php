<?php

    namespace Neoform\Locale;

    use Neoform;

    class Config extends Neoform\Config\Model {

        /**
         * Locale translations active
         *
         * @return bool
         */
        public function isActive() {
            return (bool) $this->values['active'];
        }

        /**
         * Default locale
         *
         * @return string
         */
        public function getDefault() {
            return $this->values['default'];
        }

        /**
         * Allowed locales (iso2)
         *
         * @return string[]
         */
        public function getAllowed() {
            return $this->values['allowed'];
        }

        /**
         * Which cache engine should be used to store compiled translation dictionaries
         *
         * @return string
         */
        public function getCacheEngine() {
            return $this->values['cache_engine'];
        }

        /**
         * Which cache pool should translations use to store compiled translation dictionaries
         *
         * @return string
         */
        public function getCacheEnginePoolRead() {
            return $this->values['cache_engine_read'];
        }

        /**
         * Which cache pool should translations use to store compiled translation dictionaries
         *
         * @return string
         */
        public function getCacheEnginePoolWrite() {
            return $this->values['cache_engine_write'];
        }
    }