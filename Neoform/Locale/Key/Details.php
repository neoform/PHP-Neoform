<?php

    namespace Neoform\Locale\Key;

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
            return 'Locale Key';
        }

        /**
         * Name of source identifier (eg, SQL table)
         *
         * @return string
         */
        public static function getSourceIdentifier() {
            return 'locale_key';
        }

        /**
         * Namespace for this entity
         *
         * @return string
         */
        public static function getNamespace() {
            return 'Neoform\Locale\Key';
        }

        /**
         * Cache key prefix
         *
         * @return string
         */
        public static function getCacheKeyPrefix() {
            return 'Neoform:LocaleKey';
        }

        /**
         * The primary key is auto assigned
         *
         * @return bool
         */
        public static function isPrimaryKeyAutoIncrement() {
            return true;
        }

        /**
         * Field name of the primary key
         *
         * @return string
         */
        public static function getPrimaryKeyName() {
            return 'id';
        }
    }
