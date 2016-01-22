<?php

    namespace Neoform\Acl\Role\Resource;

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
            return 'ACL Role Resource';
        }

        /**
         * Name of source identifier (eg, SQL table)
         *
         * @return string
         */
        public static function getSourceIdentifier() {
            return 'acl_role_resource';
        }

        /**
         * Namespace for this entity
         *
         * @return string
         */
        public static function getNamespace() {
            return 'Neoform\Acl\Role\Resource';
        }

        /**
         * Cache key prefix
         *
         * @return string
         */
        public static function getCacheKeyPrefix() {
            return 'Neoform:AclRoleResource';
        }
    }
