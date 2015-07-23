<?php

    namespace Neoform\Acl\Resource;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        /**
         * Creates a Acl Resource model with $info
         *
         * @param array $info
         *
         * @return model
         * @throws Input\Exception
         */
        public static function insert(array $info) {

            $input = new Input\Collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\Acl\Resource')->insert([
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
         * @throws Input\Exception
         */
        public static function update(Model $acl_resource, array $info, $crush=false) {

            $input = new Input\Collection($info);

            self::_validate_update($acl_resource, $input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\Acl\Resource')->update(
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
         * @throws Input\Exception
         */
        public static function move(Model $acl_resource, $parent_id) {

            try {
                if (! $parent_id) {
                    $parent_id = null;
                }

                if ($parent_id) {
                    $parent = new Model((int) $parent_id);
                    $parent_id = $parent->id;
                }

                if (Entity::dao('Neoform\Acl\Resource')->by_parent_name($parent_id, $acl_resource->name)) {
                    return 'a resource with that name already exists in that location';
                }

                // Check if we're attempting to add a resource to itself
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

            } catch (Exception $e) {
                return 'resource does not exist';
            }

            return Entity::dao('Neoform\Acl\Resource')->update(
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
        public static function delete(Model $acl_resource) {
            $child_resources = $acl_resource->child_acl_resource_collection();
            if (count($child_resources)) {
                self::delete_recursive($child_resources);
            }

            return Entity::dao('Neoform\Acl\Resource')->delete($acl_resource);
        }

        /**
         * Delete all resources
         *
         * @param collection $resources
         */
        protected static function delete_recursive(Collection $resources) {
            // Child resources
            $child_resources = $resources->child_acl_resource_collection();
            if (count($child_resources)) {
                self::delete_recursive($child_resources);
            }

            // Once children are dead, kill the parents
            Entity::dao('Neoform\Acl\Resource')->deleteMulti($resources);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // parent_id
            $input->parent_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($parent_id) {
                if ($parent_id->val()) {
                    try {
                        $parent_id->data('model', new \Neoform\Acl\Resource\Model($parent_id->val()));
                    } catch (\Neoform\Acl\Resource\Exception $e) {
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
                    if (Entity::dao('Neoform\Acl\Resource')->by_parent_name($input->parent_id->val(), $name->val())) {
                        $name->errors('already in use');
                    }
                });
        }

        /**
         * Validates info to update a Acl Resource model
         *
         * @param model $acl_resource
         * @param Input\Collection $input
         */
        public static function _validate_update(Model $acl_resource, Input\Collection $input) {

            // parent_id
            $input->parent_id->cast('int')->optional(true)->nullify()->digit(0, 4294967295)->callback(function($parent_id) use ($acl_resource) {
                if ($parent_id->val()) {
                    try {
                        $parent_resource = new \Neoform\Acl\Resource\Model($parent_id->val());
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

                    } catch (\Neoform\Acl\Resource\Exception $e) {
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
                    $id_arr = Entity::dao('Neoform\Acl\Resource')->by_parent_name($input->parent_id->val(), $name->val());
                    if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $acl_resource->id) {
                        $name->errors('already in use');
                    }
                });
        }
    }
