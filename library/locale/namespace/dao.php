<?php

    /**
     * Locale Namespace DAO
     */
    class locale_namespace_dao extends entity_record_dao implements locale_namespace_definition {

        const BY_NAME = 'by_name';

        /**
         * $var array $field_bindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $field_bindings = [
            'id'   => self::TYPE_INTEGER,
            'name' => self::TYPE_STRING,
        ];

        // READS

        /**
         * Get Locale Namespace ids by name
         *
         * @param string $name
         *
         * @return array of Locale Namespace ids
         */
        public function by_name($name) {
            return parent::_by_fields(
                self::BY_NAME,
                [
                    'name' => (string) $name,
                ]
            );
        }

        /**
         * Get Locale Namespace id_arr by an array of names
         *
         * @param array $name_arr an array containing names
         *
         * @return array of arrays of Locale Namespace ids
         */
        public function by_name_multi(array $name_arr) {
            $keys_arr = [];
            foreach ($name_arr as $k => $name) {
                $keys_arr[$k] = [ 'name' => (string) $name, ];
            }
            return parent::_by_fields_multi(
                self::BY_NAME,
                $keys_arr
            );
        }

        // WRITES

        /**
         * Insert Locale Namespace record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return locale_namespace_model
         */
        public function insert(array $info) {

            // Insert record
            return parent::_insert($info);
        }

        /**
         * Insert multiple Locale Namespace records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return locale_namespace_collection
         */
        public function inserts(array $infos) {

            // Insert record
            return parent::_inserts($infos);
        }

        /**
         * Updates a Locale Namespace record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param locale_namespace_model $locale_namespace record to be updated
         * @param array $info data to write to the record
         *
         * @return locale_namespace_model updated model
         */
        public function update(locale_namespace_model $locale_namespace, array $info) {

            // Update record
            return parent::_update($locale_namespace, $info);
        }

        /**
         * Delete a Locale Namespace record
         *
         * @param locale_namespace_model $locale_namespace record to be deleted
         *
         * @return bool
         */
        public function delete(locale_namespace_model $locale_namespace) {

            // Delete record
            return parent::_delete($locale_namespace);
        }

        /**
         * Delete multiple Locale Namespace records
         *
         * @param locale_namespace_collection $locale_namespace_collection records to be deleted
         *
         * @return bool
         */
        public function deletes(locale_namespace_collection $locale_namespace_collection) {

            // Delete records
            return parent::_deletes($locale_namespace_collection);
        }
    }
