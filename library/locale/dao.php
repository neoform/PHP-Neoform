<?php

    /**
     * Locale DAO
     */
    class locale_dao extends entity_record_dao implements locale_definition {

        const BY_ALL = 'by_all';

        /**
         * Get the generic bindings of the table columns
         *
         * @return array
         */
        public static function bindings() {
            return [
                'iso2' => 'string',
                'name' => 'string',
            ];
        }

        // READS

        /**
         * Get all data for all Locale records
         *
         * @return array containing all Locale records
         */
        public static function all() {
            return parent::_all(self::BY_ALL);
        }

        // WRITES

        /**
         * Insert Locale record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return locale_model
         */
        public function insert(array $info) {

            // Insert record
            $return = parent::_insert($info);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            return $return;
        }

        /**
         * Insert multiple Locale records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return locale_collection
         */
        public function inserts(array $infos) {

            // Insert records
            $return = parent::_inserts($infos);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            return $return;
        }

        /**
         * Updates a Locale record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param locale_model $locale record to be updated
         * @param array $info data to write to the record
         *
         * @return locale_model updated model
         */
        public function update(locale_model $locale, array $info) {

            // Update record
            $updated_model = parent::_update($locale, $info);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            return $updated_model;
        }

        /**
         * Delete a Locale record
         *
         * @param locale_model $locale record to be deleted
         *
         * @return bool
         */
        public function delete(locale_model $locale) {

            // Delete record
            $return = parent::_delete($locale);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            return $return;
        }

        /**
         * Delete multiple Locale records
         *
         * @param locale_collection $locale_collection records to be deleted
         *
         * @return bool
         */
        public function deletes(locale_collection $locale_collection) {

            // Delete records
            $return = parent::_deletes($locale_collection);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            return $return;
        }
    }
