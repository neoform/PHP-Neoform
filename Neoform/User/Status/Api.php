<?php

    namespace Neoform\User\Status;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        /**
         * Creates a User Status model with $info
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
                return Entity::dao('Neoform\User\Status')->insert([
                    'id'   => $input->id->val(),
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        /**
         * Update a User Status model with $info
         *
         * @param model $user_status
         * @param array $info
         * @param bool  $crush
         *
         * @return model
         * @throws Input\Exception
         */
        public static function update(Model $user_status, array $info, $crush=false) {

            $input = new Input\Collection($info);

            self::_validate_update($user_status, $input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\User\Status')->update(
                    $user_status,
                    $input->vals(
                        [
                            'id',
                            'name',
                        ],
                        $crush
                    )
                );
            }
            throw $input->exception();
        }

        /**
         * Delete a User Status
         *
         * @param model $user_status
         *
         * @return bool
         */
        public static function delete(Model $user_status) {
            return Entity::dao('Neoform\User\Status')->delete($user_status);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // id
            $input->id->cast('int')->digit(0, 255)->callback(function($id) {
                if (Entity::dao('Neoform\User\Status')->record($id->val())) {
                    $id->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->length(1, 255)->callback(function($name) {
                if (Entity::dao('Neoform\User\Status')->by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        /**
         * Validates info to update a User Status model
         *
         * @param model $user_status
         * @param Input\Collection $input
         */
        public static function _validate_update(Model $user_status, Input\Collection $input) {

            // id
            $input->id->cast('int')->optional()->digit(0, 255)->callback(function($id) use ($user_status) {
                $user_status_info = Entity::dao('Neoform\User\Status')->record($id->val());
                if ($user_status_info && (int) $user_status_info['id'] !== $user_status->id) {
                    $id->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->optional()->length(1, 255)->callback(function($name) use ($user_status) {
                $id_arr = Entity::dao('Neoform\User\Status')->by_name($name->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $user_status->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
