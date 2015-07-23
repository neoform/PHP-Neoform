<?php

    namespace Neoform\Site;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        /**
         * Creates a Site model with $info
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
                return Entity::dao('Neoform\Site')->insert([
                    'id'   => $input->id->val(),
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        /**
         * Update a Site model with $info
         *
         * @param model $site
         * @param array $info
         * @param bool  $crush
         *
         * @return model
         * @throws Input\Exception
         */
        public static function update(Model $site, array $info, $crush=false) {

            $input = new Input\Collection($info);

            self::_validate_update($site, $input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\Site')->update(
                    $site,
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
         * Delete a Site
         *
         * @param model $site
         *
         * @return bool
         */
        public static function delete(Model $site) {
            return Entity::dao('Neoform\Site')->delete($site);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // id
            $input->id->cast('int')->digit(0, 65535)->callback(function($id) {
                if (Entity::dao('Neoform\Site')->record($id->val())) {
                    $id->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->length(1, 64)->callback(function($name) {
                if (Entity::dao('Neoform\Site')->by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        /**
         * Validates info to update a Site model
         *
         * @param model $site
         * @param Input\Collection $input
         */
        public static function _validate_update(Model $site, Input\Collection $input) {

            // id
            $input->id->cast('int')->optional()->digit(0, 65535)->callback(function($id) use ($site) {
                $site_info = Entity::dao('Neoform\Site')->record($id->val());
                if ($site_info && (int) $site_info['id'] !== $site->id) {
                    $id->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->optional()->length(1, 64)->callback(function($name) use ($site) {
                $id_arr = Entity::dao('Neoform\Site')->by_name($name->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $site->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
