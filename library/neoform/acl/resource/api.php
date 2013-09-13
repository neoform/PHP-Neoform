<?php

    namespace neoform\acl\resource;

    use neoform\input;
    use neoform\entity;

    class api {

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

        public static function delete(model $acl_resource) {
            return entity::dao('acl\resource')->delete($acl_resource);
        }

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
            $input->name->cast('string')->length(1, 32)->callback(function($name) {
                if (entity::dao('acl\resource')->by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        public static function _validate_update(model $acl_resource, input\collection $input) {

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
            $input->name->cast('string')->optional()->length(1, 32)->callback(function($name) use ($acl_resource) {
                $id_arr = entity::dao('acl\resource')->by_name($name->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $acl_resource->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
