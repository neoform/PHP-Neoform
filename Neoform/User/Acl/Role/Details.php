<?php

    namespace Neoform\User\Acl\Role;

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
            return 'User ACL Role';
        }

        /**
         * Name of source identifier (eg, SQL table)
         *
         * @return string
         */
        public static function getSourceIdentifier() {
            return 'user_acl_role';
        }

        /**
         * Namespace for this entity
         *
         * @return string
         */
        public static function getNamespace() {
            return 'Neoform\User\Acl\Role';
        }

        /**
         * Cache key prefix
         *
         * @return string
         */
        public static function getCacheKeyPrefix() {
            return 'Neoform:UserAclRole';
        }
    }
