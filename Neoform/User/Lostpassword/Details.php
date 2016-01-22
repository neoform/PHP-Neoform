<?php

    namespace Neoform\User\Lostpassword;

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
            return 'User Lost Password';
        }

        /**
         * Name of source identifier (eg, SQL table)
         *
         * @return string
         */
        public static function getSourceIdentifier() {
            return 'user_lostpassword';
        }

        /**
         * Namespace for this entity
         *
         * @return string
         */
        public static function getNamespace() {
            return 'Neoform\User\Lostpassword';
        }

        /**
         * Cache key prefix
         *
         * @return string
         */
        public static function getCacheKeyPrefix() {
            return 'Neoform:UserLostpassword';
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
            return 'hash';
        }
    }
