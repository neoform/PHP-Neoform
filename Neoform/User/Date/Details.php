<?php

    namespace Neoform\User\Date;

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
            return 'User Date';
        }

        /**
         * Name of source identifier (eg, SQL table)
         *
         * @return string
         */
        public static function getSourceIdentifier() {
            return 'user_date';
        }

        /**
         * Namespace for this entity
         *
         * @return string
         */
        public static function getNamespace() {
            return 'Neoform\User\Date';
        }

        /**
         * Cache key prefix
         *
         * @return string
         */
        public static function getCacheKeyPrefix() {
            return 'Neoform:UserDate';
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
            return 'user_id';
        }
    }
