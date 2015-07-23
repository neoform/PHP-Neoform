<?php

    namespace Neoform\Locale;

    /**
     * Locale DAO
     */
    class Dao extends \Neoform\Entity\Record\Dao implements Definition {


        /**
         * $var array $fieldBindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $fieldBindings = [
            'iso2' => self::TYPE_STRING,
            'name' => self::TYPE_STRING,
        ];

        /**
         * $var array $referencedEntities list of fields (in this entity) and their related foreign entity
         *
         * @return array
         */
        protected $referencedEntities = [];

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
        public function insertMulti(array $infos) {

            // Insert record
            return parent::_insertMulti($infos);
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
        public function update(Model $locale, array $info) {

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
        public function delete(Model $locale) {

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
        public function deleteMulti(Collection $locale_collection) {

            // Delete records
            return parent::_deleteMulti($locale_collection);
        }
    }
