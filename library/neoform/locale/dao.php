<?php

    namespace neoform\locale;

    /**
     * Locale DAO
     */
    class dao extends \neoform\entity\record\dao implements definition {


        /**
         * $var array $field_bindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $field_bindings = [
            'iso2' => self::TYPE_STRING,
            'name' => self::TYPE_STRING,
        ];

        /**
         * $var array $referenced_entities list of fields (in this entity) and their related foreign entity
         *
         * @return array
         */
        protected $referenced_entities = [];

        // WRITES

        /**
         * Insert Locale record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return model
         */
        public function insert(array $info) {

            // Insert record
            return parent::_insert($info);
        }

        /**
         * Insert multiple Locale records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return collection
         */
        public function insert_multi(array $infos) {

            // Insert record
            return parent::_insert_multi($infos);
        }

        /**
         * Updates a Locale record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param model $locale record to be updated
         * @param array $info data to write to the record
         *
         * @return model updated model
         */
        public function update(model $locale, array $info) {

            // Update record
            return parent::_update($locale, $info);
        }

        /**
         * Delete a Locale record
         *
         * @param model $locale record to be deleted
         *
         * @return bool
         */
        public function delete(model $locale) {

            // Delete record
            return parent::_delete($locale);
        }

        /**
         * Delete multiple Locale records
         *
         * @param collection $locale_collection records to be deleted
         *
         * @return bool
         */
        public function delete_multi(collection $locale_collection) {

            // Delete records
            return parent::_delete_multi($locale_collection);
        }
    }
