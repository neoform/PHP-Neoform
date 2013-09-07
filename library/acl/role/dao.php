<?php

    /**
     * Acl Role DAO
     */
    class acl_role_dao extends entity_record_dao implements acl_role_definition {

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
         * Get Acl Role ids by name
         *
         * @param string $name
         *
         * @return array of Acl Role ids
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
         * Get Acl Role id_arr by an array of names
         *
         * @param array $name_arr an array containing names
         *
         * @return array of arrays of Acl Role ids
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
         * Insert Acl Role record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return acl_role_model
         */
        public function insert(array $info) {

            // Insert record
            return parent::_insert($info);
        }

        /**
         * Insert multiple Acl Role records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return acl_role_collection
         */
        public function insert_multi(array $infos) {

            // Insert record
            return parent::_insert_multi($infos);
        }

        /**
         * Updates a Acl Role record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param acl_role_model $acl_role record to be updated
         * @param array $info data to write to the record
         *
         * @return acl_role_model updated model
         */
        public function update(acl_role_model $acl_role, array $info) {

            // Update record
            return parent::_update($acl_role, $info);
        }

        /**
         * Delete a Acl Role record
         *
         * @param acl_role_model $acl_role record to be deleted
         *
         * @return bool
         */
        public function delete(acl_role_model $acl_role) {

            // Delete record
            return parent::_delete($acl_role);
        }

        /**
         * Delete multiple Acl Role records
         *
         * @param acl_role_collection $acl_role_collection records to be deleted
         *
         * @return bool
         */
        public function delete_multi(acl_role_collection $acl_role_collection) {

            // Delete records
            return parent::_delete_multi($acl_role_collection);
        }
    }
