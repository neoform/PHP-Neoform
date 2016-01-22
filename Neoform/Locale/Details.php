<?php

    namespace Neoform\Locale;

    /**
     * Entity definition trait
     */
    trait Details {

        /**
         * Label to identify this entity
         *
         * @return string
         */
        public static function getLabel() {
            return 'Locale';
        }

        /**
         * Name of source identifier (eg, SQL table)
         *
         * @return string
         */
        public static function getSourceIdentifier() {
            return 'locale';
        }

        /**
         * Namespace for this entity
         *
         * @return string
         */
        public static function getNamespace() {
            return 'Neoform\Locale';
        }

        /**
         * Cache key prefix
         *
         * @return string
         */
        public static function getCacheKeyPrefix() {
            return 'Neoform:Locale';
        }

        /**
         * The primary key is auto assigned
         *
         * @return bool
         */
        public static function isPrimaryKeyAutoIncrement() {
            return false;
        }

        /**
         * Field name of the primary key
         *
         * @return string
         */
        public static function getPrimaryKeyName() {
            return 'iso2';
        }
    }
