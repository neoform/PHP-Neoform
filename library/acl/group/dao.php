<?php

    /**
     * Acl Group DAO
     */
    class acl_group_dao extends entity_record_dao implements acl_group_definition {

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
         * Get Acl Group ids by name
         *
         * @param string $name
         *
         * @return array of Acl Group ids
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
         * Get Acl Group id_arr by an array of names
         *
         * @param array $name_arr an array containing names
         *
         * @return array of arrays of Acl Group ids
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
         * Insert Acl Group record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return acl_group_model
         */
        public function insert(array $info) {

            // Insert record
            return parent::_insert($info);
        }

        /**
         * Insert multiple Acl Group records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return acl_group_collection
         */
        public function insert_multi(array $infos) {

            // Insert record
            return parent::_insert_multi($infos);
        }

        /**
         * Updates a Acl Group record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param acl_group_model $acl_group record to be updated
         * @param array $info data to write to the record
         *
         * @return acl_group_model updated model
         */
        public function update(acl_group_model $acl_group, array $info) {

            // Update record
            return parent::_update($acl_group, $info);
        }

        /**
         * Delete a Acl Group record
         *
         * @param acl_group_model $acl_group record to be deleted
         *
         * @return bool
         */
        public function delete(acl_group_model $acl_group) {

            // Delete record
            return parent::_delete($acl_group);
        }

        /**
         * Delete multiple Acl Group records
         *
         * @param acl_group_collection $acl_group_collection records to be deleted
         *
         * @return bool
         */
        public function delete_multi(acl_group_collection $acl_group_collection) {

            // Delete records
            return parent::_delete_multi($acl_group_collection);
        }
    }
