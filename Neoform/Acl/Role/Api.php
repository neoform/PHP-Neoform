<?php

    namespace Neoform\Acl\Role;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        /**
         * Creates a Acl Role model with $info
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
                return Entity::dao('Neoform\Acl\Role')->insert([
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
         * @throws Input\Exception
         */
        public static function update(Model $acl_role, array $info, $crush=false) {

            $input = new Input\Collection($info);

            self::_validate_update($acl_role, $input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\Acl\Role')->update(
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
        public static function delete(Model $acl_role) {
            return Entity::dao('Neoform\Acl\Role')->delete($acl_role);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // name
            $input->name->cast('string')->length(1, 64)->callback(function($name) {
                if (Entity::dao('Neoform\Acl\Role')->by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        /**
         * Validates info to update a Acl Role model
         *
         * @param model $acl_role
         * @param Input\Collection $input
         */
        public static function _validate_update(Model $acl_role, Input\Collection $input) {

            // name
            $input->name->cast('string')->optional()->length(1, 64)->callback(function($name) use ($acl_role) {
                $id_arr = Entity::dao('Neoform\Acl\Role')->by_name($name->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $acl_role->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
