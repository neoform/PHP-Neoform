<?php

    namespace Neoform\Acl\Group\User;

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
            return 'ACL Group User Link';
        }

        /**
         * Name of source identifier (eg, SQL table)
         *
         * @return string
         */
        public static function getSourceIdentifier() {
            return 'acl_group_user';
        }

        /**
         * Namespace for this entity
         *
         * @return string
         */
        public static function getNamespace() {
            return 'Neoform\Acl\Group\User';
        }

        /**
         * Cache key prefix
         *
         * @return string
         */
        public static function getCacheKeyPrefix() {
            return 'Neoform:AclGroupUser';
        }
    }
