<?php

    namespace neoform;

    class acl_resource_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('acl_resource')->insert([
                    'parent_id' => $input->parent_id->val(),
                    'name'      => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(acl_resource_model $acl_resource, array $info, $crush=false) {

            $input = new input_collection($info);

            self::_validate_update($acl_resource, $input);

            if ($input->is_valid()) {
                return entity::dao('acl_resource')->update(
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

        public static function delete(acl_resource_model $acl_resource) {
            return entity::dao('acl_resource')->delete($acl_resource);
        }

        public static function _validate_insert(input_collection $input) {

            // parent_id
            $input->parent_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($parent_id){
                if ($parent_id->val()) {
                    try {
                        $parent_id->data('model', new acl_resource_model($parent_id->val()));
                    } catch (acl_resource_exception $e) {
                        $parent_id->errors($e->getMessage());
                    }
                }
            });

            // name
            $input->name->cast('string')->length(1, 32)->callback(function($name) {
                if (entity::dao('acl_resource')->by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        public static function _validate_update(acl_resource_model $acl_resource, input_collection $input) {

            // parent_id
            $input->parent_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($parent_id){
                if ($parent_id->val()) {
                    try {
                        $parent_id->data('model', new acl_resource_model($parent_id->val()));
                    } catch (acl_resource_exception $e) {
                        $parent_id->errors($e->getMessage());
                    }
                }
            });

            // name
            $input->name->cast('string')->optional()->length(1, 32)->callback(function($name) use ($acl_resource) {
                $id_arr = entity::dao('acl_resource')->by_name($name->val());
                if (\is_array($id_arr) && $id_arr && (int) \current($id_arr) !== $acl_resource->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
