<?php

    namespace neoform\acl\resource;

    use neoform;

    class lib {

        /**
         * Get the resource ID from a resource slug (eg, "admin/acl/role")
         *
         * @param string $slug
         *
         * @return int|null
         * @throws exception
         */
        public static function id_from_slug($slug) {
            if ($resource_names = preg_split('`\s*/\s*`', $slug, -1, PREG_SPLIT_NO_EMPTY)) {
                $parent_id = null;
                foreach ($resource_names as $resource_name) {
                    if ($resource_model = neoform\entity::dao('acl\resource')->by_parent_name($parent_id, $resource_name)) {
                        $parent_id = (int) reset($resource_model);
                    } else {
                        throw new exception("Resource \"{$slug}\" does not exist");
                    }
                }
                return $parent_id;
            }
        }
    }
