<?php

    namespace Neoform\Acl\Group;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        /**
         * Creates a Acl Group model with $info
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
                return Entity::dao('Neoform\Acl\Group')->insert([
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
         * @throws Input\Exception
         */
        public static function update(Model $acl_group, array $info, $crush=false) {

            $input = new Input\Collection($info);

            self::_validate_update($acl_group, $input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\Acl\Group')->update(
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
        public static function delete(Model $acl_group) {
            return Entity::dao('Neoform\Acl\Group')->delete($acl_group);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // name
            $input->name->cast('string')->length(1, 64)->callback(function($name) {
                if (Entity::dao('Neoform\Acl\Group')->by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        /**
         * Validates info to update a Acl Group model
         *
         * @param model $acl_group
         * @param Input\Collection $input
         */
        public static function _validate_update(Model $acl_group, Input\Collection $input) {

            // name
            $input->name->cast('string')->length(1, 64)->callback(function($name) use ($acl_group) {
                $id_arr = Entity::dao('Neoform\Acl\Group')->by_name($name->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $acl_group->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
