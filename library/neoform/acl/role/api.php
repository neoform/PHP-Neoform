<?php

    namespace neoform\acl\role;

    use neoform\input;
    use neoform\entity;

    class api {

        public static function insert(array $info) {

            $input = new input\collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('neoform\acl\role')->insert([
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(model $acl_role, array $info, $crush=false) {

            $input = new input\collection($info);

            self::_validate_update($acl_role, $input);

            if ($input->is_valid()) {
                return entity::dao('neoform\acl\role')->update(
                    $acl_role,
                    $input->vals(
                        [
                            'name',
                        ],
                        $crush
                    )
                );
            }
            throw $input->exception();
        }

        public static function delete(model $acl_role) {
            return entity::dao('neoform\acl\role')->delete($acl_role);
        }

        public static function _validate_insert(input\collection $input) {

            // name
            $input->name->cast('string')->length(1, 64)->callback(function($name) {
                if (entity::dao('neoform\acl\role')->by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        public static function _validate_update(model $acl_role, input\collection $input) {

            // name
            $input->name->cast('string')->optional()->length(1, 64)->callback(function($name) use ($acl_role) {
                $id_arr = entity::dao('neoform\acl\role')->by_name($name->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $acl_role->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
