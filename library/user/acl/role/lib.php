<?php

    class user_acl_role_lib {

        /**
         * Checks if a collection of roles has access to the necessary resources
         *
         * @param acl_role_collection $roles
         * @param array               $resource_names
         *
         * @return bool
         * @throws user_acl_role_exception
         */
        public static function roles_have_resources(acl_role_collection $roles, array $resource_names) {

            // No resources needed? You may proceed.
            if (! $resource_names) {
                return true;
            }

            // Don't have any roles? You clearly don't have permission.
            if (! $roles) {
                return false;
            }

            // Collect all the resources in these roles.
            $role_resource_ids = $roles->acl_resource_collection()->field('id');

            // No resources in your roles? (that's weird) You may not continue.
            if (! $role_resource_ids) {
                return false;
            }

            foreach (acl_resource_dao::by_name_multi($resource_names) as $k => $resource_ids) {
                if ($resource_id = current($resource_ids)) {
                    // Don't have access to this resource? Sorry...
                    if (! in_array($resource_id, $role_resource_ids)) {
                        return false;
                    }
                } else {
                    throw new user_acl_role_exception('Resource "' . $resource_names[$k] . '" does not exist');
                }
            }

            // Everything looks good, you have access.
            return true;
        }
    }
