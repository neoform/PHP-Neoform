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
         * @return Model
         * @throws Input\Exception
         */
        public static function insert(array $info) {

            $input = new Input\Collection($info);

            self::_validate_insert($input);

            if ($input->isValid()) {
                return Dao::get()->insert(
                    $input->getVals([
                        'id',
                        'name',
                    ])
                );
            }
            throw $input->getException();
        }

        /**
         * Update a Site model with $info
         *
         * @param Model $site
         * @param array $info
         * @param bool  $includeEmpty
         *
         * @return Model
         * @throws Input\Exception
         */
        public static function update(Model $site, array $info, $includeEmpty=false) {

            $input = new Input\Collection($info);

            self::_validate_update($site, $input, $includeEmpty);

            if ($input->isValid()) {
                return Dao::get()->update(
                    $site,
                    $input->getVals(
                        [
                            'id',
                            'name',
                        ],
                        $includeEmpty
                    )
                );
            }
            throw $input->getException();
        }

        /**
         * Delete a Site
         *
         * @param Model $site
         *
         * @return bool
         */
        public static function delete(Model $site) {
            return Dao::get()->delete($site);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // id
            $input->validate('id', 'int')
                ->requireDigit(0, 65535)
                ->callback(function(Input\Input $id) {
                    if (Dao::get()->record($id->getVal())) {
                        $id->setErrors('already in use');
                    }
                });

            // name
            $input->validate('name', 'string')
                ->requireLength(1, 64)
                ->callback(function(Input\Input $name) {
                    if (Dao::get()->by_name($name->getVal())) {
                        $name->setErrors('already in use');
                    }
                });
        }

        /**
         * Validates info to update a Site model
         *
         * @param Model $site
         * @param Input\Collection $input
         * @param bool $includeEmpty
         */
        public static function _validate_update(Model $site, Input\Collection $input, $includeEmpty) {

            // id
            $input->validate('id', 'int', !$includeEmpty)
                ->requireDigit(0, 65535)
                ->callback(function(Input\Input $id) use ($site) {
                    $site_info = Dao::get()->record($id->getVal());
                    if ($site_info && (int) $site_info['id'] !== $site->id) {
                        $id->setErrors('already in use');
                    }
                });

            // name
            $input->validate('name', 'string', !$includeEmpty)
                ->requireLength(1, 64)
                ->callback(function(Input\Input $name) use ($site) {
                    $id_arr = Dao::get()->by_name($name->getVal());
                    if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $site->id) {
                        $name->setErrors('already in use');
                    }
                });
        }
    }
