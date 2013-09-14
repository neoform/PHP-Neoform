<?php

    namespace neoform\acl\role;

    use neoform\input;
    use neoform\entity;

    class api {

        /**
         * Creates a Acl Role model with $info
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
                return entity::dao('acl\role')->insert([
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        /**
         * Update a Acl Role model with $info
         *
         * @param model $acl_role
         * @param array $info
         * @param bool  $crush
         *
         * @return model
         * @throws input\exception
         */
        public static function update(model $acl_role, array $info, $crush=false) {

            $input = new input\collection($info);

            self::_validate_update($acl_role, $input);

            if ($input->is_valid()) {
                return entity::dao('acl\role')->update(
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

        /**
         * Delete a Acl Role
         *
         * @param model $acl_role
         *
         * @return bool
         */
        public static function delete(model $acl_role) {
            return entity::dao('acl\role')->delete($acl_role);
        }

        /**
         * Validates info to for insert
         *
         * @param input\collection $input
         */
        public static function _validate_insert(input\collection $input) {

            // name
            $input->name->cast('string')->length(1, 64)->callback(function($name) {
                if (entity::dao('acl\role')->by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        /**
         * Validates info to update a Acl Role model
         *
         * @param model $acl_role
         * @param input\collection $input
         */
        public static function _validate_update(model $acl_role, input\collection $input) {

            // name
            $input->name->cast('string')->optional()->length(1, 64)->callback(function($name) use ($acl_role) {
                $id_arr = entity::dao('acl\role')->by_name($name->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $acl_role->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
