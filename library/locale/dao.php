<?php

    class locale_dao extends record_dao implements locale_definition {

        const BY_ALL = 'by_all';

        public static function castings() {
            return [
                'iso2' => 'string',
                'name' => 'string',
            ];
        }

        // READS

        public static function all() {
            return parent::_all(self::BY_ALL);
        }

        // WRITES

        public static function insert(array $info) {
            $return = parent::_insert($info);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            return $return;
        }

        public static function inserts(array $infos) {
            $return = parent::_inserts($infos);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            return $return;
        }

        public static function update(locale_model $locale, array $info) {
            $updated_model = parent::_update($locale, $info);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            return $updated_model;
        }

        public static function delete(locale_model $locale) {
            $return = parent::_delete($locale);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            return $return;
        }

        public static function deletes(locale_collection $locales) {
            $return = parent::_deletes($locales);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            return $return;
        }

    }
