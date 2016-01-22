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
         * @return Model
         * @throws Input\Exception
         */
        public static function insert(array $info) {

            $input = new Input\Collection($info);

            self::_validate_insert($input);

            if ($input->isValid()) {
                $return = Dao::get()->insert(
                    $input->getVals([
                        'parent_id',
                        'name',
                    ])
                );
                return $return;
            }
            throw $input->getException();
        }

        /**
         * Update a Acl Resource model with $info
         *
         * @param Model $acl_resource
         * @param array $info
         * @param bool  $includeEmpty
         *
         * @return Model
         * @throws Input\Exception
         */
        public static function update(Model $acl_resource, array $info, $includeEmpty=true) {

            $input = new Input\Collection($info);

            self::_validate_update($acl_resource, $input, $includeEmpty);

            if ($input->isValid()) {
                return Dao::get()->update(
                    $acl_resource,
                    $input->getVals(
                        [
                            'parent_id',
                            'name',
                        ],
                        $includeEmpty
                    )
                );
            }
            throw $input->getException();
        }

        /**
         * Move ACL resource
         *
         * @param Model   $acl_resource
         * @param integer $parent_id
         *
         * @return Model
         * @throws Input\Exception
         */
        public static function move(Model $acl_resource, $parent_id) {

            try {
                if (! $parent_id) {
                    $parent_id = null;
                }

                if ($parent_id) {
                    $parent = Model::fromPk((int) $parent_id);
                    $parent_id = $parent->id;
                }

                if (Dao::get()->by_parent_name($parent_id, $acl_resource->name)) {
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

            return Dao::get()->update(
                $acl_resource,
                [
                    'parent_id' => $parent_id,
                ]
            );
        }

        /**
         * Delete a ACL Resource
         *
         * @param Model $acl_resource
         *
         * @return bool
         */
        public static function delete(Model $acl_resource) {
            $child_resources = $acl_resource->child_acl_resource_collection();
            if (count($child_resources)) {
                self::delete_recursive($child_resources);
            }

            return Dao::get()->delete($acl_resource);
        }

        /**
         * Delete all resources
         *
         * @param Collection $resources
         */
        protected static function delete_recursive(Collection $resources) {
            // Child resources
            $child_resources = $resources->child_acl_resource_collection();
            if (count($child_resources)) {
                self::delete_recursive($child_resources);
            }

            // Once children are dead, kill the parents
            Dao::get()->deleteMulti($resources);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // parent_id
            $input->validate('parent_id', 'int', true)
                ->requireDigit(0, 4294967295)
                ->callback(function(Input\Input $parent_id) {
                    if ($parent_id->getVal()) {
                        try {
                            $parent_id->setData('model', \Neoform\Acl\Resource\Model::fromPk($parent_id->getVal()));
                        } catch (\Neoform\Acl\Resource\Exception $e) {
                            $parent_id->setErrors($e->getMessage());
                        }
                    }
                });

            // name
            $input->validate('name', 'string')
                ->requireLength(1, 32)
                ->trim()
                ->matchRegex('`^([0-9a-z\._\-]*)$`i', 'must only contain: a-z 0-9 .-_')
                ->callback(function(Input\Input $name) use ($input) {
                    if (Dao::get()->by_parent_name($input->parent_id->getVal(), $name->getVal())) {
                        $name->setErrors('already in use');
                    }
                });
        }

        /**
         * Validates info to update a Acl Resource model
         *
         * @param Model            $acl_resource
         * @param Input\Collection $input
         * @param bool             $includeEmpty
         */
        public static function _validate_update(Model $acl_resource, Input\Collection $input, $includeEmpty) {

            // parent_id
            $input->validate('parent_id', 'int', true)
                ->requireDigit(0, 4294967295)
                ->callback(function(Input\Input $parent_id) use ($acl_resource) {
                    if ($parent_id->getVal()) {
                        try {
                            $parent_resource = \Neoform\Acl\Resource\Model::fromPk($parent_id->getVal());
                            $parent_id->setData('model', $parent_resource);

                            // Check if we're attempting to add a resource to itself
                            if ($parent_resource->id === $acl_resource->id) {
                                $parent_id->setErrors('cannot move a resource into itself');
                                return;
                            }

                            foreach ($parent_resource->ancestors() as $ancestor) {
                                if ($acl_resource->id === $ancestor->id) {
                                    $parent_id->setErrors('cannot move a resource into a child of itself');
                                    return;
                                }
                            }

                        } catch (\Neoform\Acl\Resource\Exception $e) {
                            $parent_id->setErrors($e->getMessage());
                        }
                    }
                });

            // name
            $input->validate('name', 'string', !$includeEmpty)
                ->requireLength(1, 32)
                ->matchRegex('`^([0-9a-z\._\-]*)$`i', 'must only contain: a-z 0-9 .-_')
                ->callback(function(Input\Input $name) use ($acl_resource, $input) {
                    $id_arr = Dao::get()->by_parent_name($input->parent_id->getVal(), $name->getVal());
                    if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $acl_resource->id) {
                        $name->setErrors('already in use');
                    }
                });
        }
    }
