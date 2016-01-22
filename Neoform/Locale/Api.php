<?php

    namespace Neoform\Locale;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        /**
         * Creates a Locale model with $info
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
                        'iso2',
                        'name',
                    ])
                );
            }
            throw $input->getException();
        }

        /**
         * Update a Locale model with $info
         *
         * @param Model $locale
         * @param array $info
         * @param bool  $includeEmpty
         *
         * @return Model
         * @throws Input\Exception
         */
        public static function update(Model $locale, array $info, $includeEmpty=false) {

            $input = new Input\Collection($info);

            self::_validate_update($locale, $input, $includeEmpty);

            if ($input->isValid()) {
                return Dao::get()->update(
                    $locale,
                    $input->getVals(
                        [
                            'iso2',
                            'name',
                        ],
                        $includeEmpty
                    )
                );
            }
            throw $input->getException();
        }

        /**
         * Delete a Locale
         *
         * @param Model $locale
         *
         * @return bool
         */
        public static function delete(Model $locale) {
            return Dao::get()->delete($locale);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // iso2
            $input->validate('iso2', 'string')
                ->requireLength(2, 2)
                ->callback(function(Input\Input $iso2) {
                    if (Dao::get()->record($iso2->getVal())) {
                        $iso2->setErrors('already in use');
                    }
                });

            // name
            $input->validate('name', 'string')
                ->requireLength(1, 255);
        }

        /**
         * Validates info to update a Locale model
         *
         * @param Model $locale
         * @param Input\Collection $input
         * @param bool $includeEmpty
         */
        public static function _validate_update(Model $locale, Input\Collection $input, $includeEmpty) {

            // iso2
            $input->validate('iso2', 'string', !$includeEmpty)
                ->requireLength(2, 2)
                ->callback(function(Input\Input $iso2) use ($locale) {
                    $locale_info = Dao::get()->record($iso2->getVal());
                    if ($locale_info && (string) $locale_info['iso2'] !== $locale->iso2) {
                        $iso2->setErrors('already in use');
                    }
                });

            // name
            $input->validate('name', 'string', !$includeEmpty)
                ->requireLength(1, 255);
        }
    }
