<?php

    /**
     * User Hashmethod DAO
     */
    class user_hashmethod_dao extends entity_record_dao implements user_hashmethod_definition {

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
         * Get User Hashmethod ids by name
         *
         * @param string $name
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of User Hashmethod ids
         */
        public function by_name($name, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_NAME,
                [
                    'name' => (string) $name,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get User Hashmethod id_arr by an array of names
         *
         * @param array $name_arr an array containing names
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays of User Hashmethod ids
         */
        public function by_name_multi(array $name_arr, array $order_by=null, $offset=null, $limit=null) {
            $keys_arr = [];
            foreach ($name_arr as $k => $name) {
                $keys_arr[$k] = [ 'name' => (string) $name, ];
            }
            return parent::_by_fields_multi(
                self::BY_NAME,
                $keys_arr,
                $order_by,
                $offset,
                $limit
            );
        }

        // WRITES

        /**
         * Insert User Hashmethod record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return user_hashmethod_model
         */
        public function insert(array $info) {

            // Insert record
            return parent::_insert($info);
        }

        /**
         * Insert multiple User Hashmethod records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return user_hashmethod_collection
         */
        public function inserts(array $infos) {

            // Insert record
            return parent::_inserts($infos);
        }

        /**
         * Updates a User Hashmethod record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param user_hashmethod_model $user_hashmethod record to be updated
         * @param array $info data to write to the record
         *
         * @return user_hashmethod_model updated model
         */
        public function update(user_hashmethod_model $user_hashmethod, array $info) {

            // Update record
            return parent::_update($user_hashmethod, $info);
        }

        /**
         * Delete a User Hashmethod record
         *
         * @param user_hashmethod_model $user_hashmethod record to be deleted
         *
         * @return bool
         */
        public function delete(user_hashmethod_model $user_hashmethod) {

            // Delete record
            return parent::_delete($user_hashmethod);
        }

        /**
         * Delete multiple User Hashmethod records
         *
         * @param user_hashmethod_collection $user_hashmethod_collection records to be deleted
         *
         * @return bool
         */
        public function deletes(user_hashmethod_collection $user_hashmethod_collection) {

            // Delete records
            return parent::_deletes($user_hashmethod_collection);
        }
    }
