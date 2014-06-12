<?php

    namespace neoform\acl\group;

    use neoform\input;
    use neoform\entity;

    class api {

        /**
         * Creates a Acl Group model with $info
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
                return entity::dao('acl\group')->insert([
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        /**
         * Update a Acl Group model with $info
         *
         * @param model $acl_group
         * @param array $info
         * @param bool  $crush
         *
         * @return model
         * @throws input\exception
         */
        public static function update(model $acl_group, array $info, $crush=false) {

            $input = new input\collection($info);

            self::_validate_update($acl_group, $input);

            if ($input->is_valid()) {
                return entity::dao('acl\group')->update(
                    $acl_group,
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
         * Delete a Acl Group
         *
         * @param model $acl_group
         *
         * @return bool
         */
        public static function delete(model $acl_group) {
            return entity::dao('acl\group')->delete($acl_group);
        }

        /**
         * Validates info to for insert
         *
         * @param input\collection $input
         */
        public static function _validate_insert(input\collection $input) {

            // name
            $input->name->cast('string')->length(1, 64)->callback(function($name) {
                if (entity::dao('acl\group')->by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        /**
         * Validates info to update a Acl Group model
         *
         * @param model $acl_group
         * @param input\collection $input
         */
        public static function _validate_update(model $acl_group, input\collection $input) {

            // name
            $input->name->cast('string')->length(1, 64)->callback(function($name) use ($acl_group) {
                $id_arr = entity::dao('acl\group')->by_name($name->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $acl_group->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
