<?php

    namespace Neoform\Locale\Key;

    use Neoform\Input;
    use Neoform\Entity;
    use Neoform;

    class Api {

        /**
         * Creates a Locale Key model with $info
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
                $locale_key = Dao::get()->insert(
                    $input->getVals([
                        'body',
                        'locale',
                        'namespace_id',
                    ])
                );
                Neoform\Locale\Lib::flushByLocaleNamespace($locale_key->locale, $locale_key->locale_namespace());
                return $locale_key;
            }
            throw $input->getException();
        }

        /**
         * Update a Locale Key model with $info
         *
         * @param Model $locale_key
         * @param array $info
         * @param bool  $includeEmpty
         *
         * @return Model
         * @throws Input\Exception
         */
        public static function update(Model $locale_key, array $info, $includeEmpty=false) {

            $input = new Input\Collection($info);

            self::_validate_update($locale_key, $input, $includeEmpty);

            if ($input->isValid()) {
                $updated_locale_key = Dao::get()->update(
                    $locale_key,
                    $input->getVals(
                        [
                            'body',
                            'locale',
                            'namespace_id',
                        ],
                        $includeEmpty
                    )
                );

                Neoform\Locale\Lib::flushByLocaleNamespace($locale_key->locale, $locale_key->locale_namespace());
                Neoform\Locale\Lib::flushByLocaleNamespace($updated_locale_key->locale, $updated_locale_key->locale_namespace());

                return $updated_locale_key;
            }
            throw $input->getException();
        }

        /**
         * Delete a Locale Key
         *
         * @param Model $locale_key
         *
         * @return bool
         */
        public static function delete(Model $locale_key) {
            return Dao::get()->delete($locale_key);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // body
            $input->validate('body', 'string')
                ->requireLength(1, 255);

            // locale
            $input->validate('locale', 'string')
                ->requireLength(1, 2)
                ->callback(function(Input\Input $locale) {
                    try {
                        $locale->setData('model', \Neoform\Locale\Model::fromPk($locale->getVal()));
                    } catch (\Neoform\Locale\Exception $e) {
                        $locale->setErrors($e->getMessage());
                    }
                });

            // namespace_id
            $input->validate('namespace_id', 'int')
                ->requireDigit(0, 4294967295)
                ->callback(function(Input\Input $namespace_id) {
                    try {
                        $namespace_id->setData('model', \Neoform\Locale\Nspace\Model::fromPk($namespace_id->getVal()));
                    } catch (\Neoform\Locale\Nspace\Exception $e) {
                        $namespace_id->setErrors($e->getMessage());
                    }
                });
        }

        /**
         * Validates info to update a Locale Key model
         *
         * @param Model $locale_key
         * @param Input\Collection $input
         * @param bool $includeEmpty
         */
        public static function _validate_update(Model $locale_key, Input\Collection $input, $includeEmpty) {

            // body
            $input->validate('body', 'string', !$includeEmpty)
                ->requireLength(1, 255);

            // locale
            $input->validate('locale', 'string', !$includeEmpty)
                ->requireLength(1, 2)
                ->callback(function(Input\Input $locale) {
                    try {
                        $locale->setData('model', \Neoform\Locale\Model::fromPk($locale->getVal()));
                    } catch (\Neoform\Locale\Exception $e) {
                        $locale->setErrors($e->getMessage());
                    }
                });

            // namespace_id
            $input->validate('namespace_id', 'int', !$includeEmpty)
                ->requireDigit(0, 4294967295)
                ->callback(function(Input\Input $namespace_id) {
                    try {
                        $namespace_id->setData('model', \Neoform\Locale\Nspace\Model::fromPk($namespace_id->getVal()));
                    } catch (\Neoform\Locale\Nspace\Exception $e) {
                        $namespace_id->setErrors($e->getMessage());
                    }
                });
        }
    }
