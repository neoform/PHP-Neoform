<?php

    namespace neoform\acl\resource;

    use neoform\input;
    use neoform\entity;

    class api {

        /**
         * Creates a Acl Resource model with $info
         *
         * @param array $info
         *
         * @return model
         * @throws input\exception
         */
        public static function insert(array $info) {

            $input = new input\collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('acl\resource')->insert([
                    'parent_id' => $input->parent_id->val(),
                    'name'      => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        /**
         * Update a Acl Resource model with $info
         *
         * @param model $acl_resource
         * @param array $info
         * @param bool  $crush
         *
         * @return model
         * @throws input\exception
         */
        public static function update(model $acl_resource, array $info, $crush=false) {

            $input = new input\collection($info);

            self::_validate_update($acl_resource, $input);

            if ($input->is_valid()) {
                return entity::dao('acl\resource')->update(
                    $acl_resource,
                    $input->vals(
                        [
                            'parent_id',
                            'name',
                        ],
                        $crush
                    )
                );
            }
            throw $input->exception();
        }

        /**
         * Move ACL resource
         *
         * @param model   $acl_resource
         * @param integer $parent_id
         *
         * @return model
         * @throws input\exception
         */
        public static function move(model $acl_resource, $parent_id) {

            try {
                if (! $parent_id) {
                    $parent_id = null;
                }

                if ($parent_id) {
                    $parent = new model((int) $parent_id);
                    $parent_id = $parent->id;
                }

                if (\neoform\entity::dao('acl\resource')->by_parent_name($parent_id, $acl_resource->name)) {
                    return 'a resource with that name already exists in that location';
                }

                // Check if we're attempting to add a category to itself
                if ($parent_id) {
                    if ($parent->id === $acl_resource->id) {
                        return 'cannot move a resource into itself';
                    }

                    foreach ($parent->ancestors() as $ancestor) {
                        if ($acl_resource->id === $ancestor->id) {
                            return 'cannot move a resource into a child of itself';
                        }
                    }
                }

            } catch (\neoform\sa\category\exception $e) {
                return 'resource does not exist';
            }

            return entity::dao('acl\resource')->update(
                $acl_resource,
                [
                    'parent_id' => $parent_id,
                ]
            );
        }

        /**
         * Delete a ACL Resource
         *
         * @param model $acl_resource
         *
         * @return bool
         */
        public static function delete(model $acl_resource) {
            $child_resources = $acl_resource->child_acl_resource_collection();
            if (count($child_resources)) {
                self::delete_recursive($child_resources);
            }

            return entity::dao('acl\resource')->delete($acl_resource);
        }

        /**
         * Delete all resources
         *
         * @param collection $resources
         */
        protected static function delete_recursive(collection $resources) {
            // Child resources
            $child_resources = $resources->child_acl_resource_collection();
            if (count($child_resources)) {
                self::delete_recursive($child_resources);
            }

            // Once children are dead, kill the parents
            entity::dao('acl\resource')->delete_multi($resources);
        }

        /**
         * Validates info to for insert
         *
         * @param input\collection $input
         */
        public static function _validate_insert(input\collection $input) {

            // parent_id
            $input->parent_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($parent_id) {
                if ($parent_id->val()) {
                    try {
                        $parent_id->data('model', new \neoform\acl\resource\model($parent_id->val()));
                    } catch (\neoform\acl\resource\exception $e) {
                        $parent_id->errors($e->getMessage());
                    }
                }
            });

            // name
            $input->name->cast('string')
                ->length(1, 32)
                ->trim()
                ->match_regex('`^([0-9a-z\._\-]*)$`i', 'must only contain: a-z 0-9 .-_')
                ->callback(function($name) use ($input) {
                    if (entity::dao('acl\resource')->by_parent_name($input->parent_id->val(), $name->val())) {
                        $name->errors('already in use');
                    }
                });
        }

        /**
         * Validates info to update a Acl Resource model
         *
         * @param model $acl_resource
         * @param input\collection $input
         */
        public static function _validate_update(model $acl_resource, input\collection $input) {

            // parent_id
            $input->parent_id->cast('int')->optional(true)->nullify()->digit(0, 4294967295)->callback(function($parent_id) use ($acl_resource) {
                if ($parent_id->val()) {
                    try {
                        $parent_resource = new \neoform\acl\resource\model($parent_id->val());
                        $parent_id->data('model', $parent_resource);

                        // Check if we're attempting to add a resource to itself
                        if ($parent_resource->id === $acl_resource->id) {
                            $parent_id->errors('cannot move a resource into itself');
                            return;
                        }

                        foreach ($parent_resource->ancestors() as $ancestor) {
                            if ($acl_resource->id === $ancestor->id) {
                                $parent_id->errors('cannot move a resource into a child of itself');
                                return;
                            }
                        }

                    } catch (\neoform\acl\resource\exception $e) {
                        $parent_id->errors($e->getMessage());
                    }
                }
            });

            // name
            $input->name->cast('string')
                ->optional()
                ->length(1, 32)
                ->match_regex('`^([0-9a-z\._\-]*)$`i', 'must only contain: a-z 0-9 .-_')
                ->callback(function($name) use ($acl_resource, $input) {
                    $id_arr = entity::dao('acl\resource')->by_parent_name($input->parent_id->val(), $name->val());
                    if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $acl_resource->id) {
                        $name->errors('already in use');
                    }
                });
        }
    }
