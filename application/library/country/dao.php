<?php

    /**
     * Country DAO
     */
    class country_dao extends entity_record_dao implements country_definition {

        const BY_NAME_NORMALIZED = 'by_name_normalized';
        const BY_ISO2            = 'by_iso2';
        const BY_ISO3            = 'by_iso3';
        const BY_NAME            = 'by_name';

        /**
         * $var array $field_bindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $field_bindings = [
            'id'              => self::TYPE_INTEGER,
            'name'            => self::TYPE_STRING,
            'name_normalized' => self::TYPE_STRING,
            'iso2'            => self::TYPE_STRING,
            'iso3'            => self::TYPE_STRING,
        ];

        // READS

        /**
         * Get Country ids by name_normalized
         *
         * @param string $name_normalized
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of Country ids
         */
        public function by_name_normalized($name_normalized, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_NAME_NORMALIZED,
                [
                    'name_normalized' => (string) $name_normalized,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get Country ids by iso2
         *
         * @param string $iso2
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of Country ids
         */
        public function by_iso2($iso2, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_ISO2,
                [
                    'iso2' => (string) $iso2,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get Country ids by iso3
         *
         * @param string $iso3
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of Country ids
         */
        public function by_iso3($iso3, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_ISO3,
                [
                    'iso3' => (string) $iso3,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get Country ids by name
         *
         * @param string $name
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of Country ids
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
         * Get Country id_arr by an array of name_normalizeds
         *
         * @param array $name_normalized_arr an array containing name_normalizeds
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays of Country ids
         */
        public function by_name_normalized_multi(array $name_normalized_arr, array $order_by=null, $offset=null, $limit=null) {
            $keys_arr = [];
            foreach ($name_normalized_arr as $k => $name_normalized) {
                $keys_arr[$k] = [ 'name_normalized' => (string) $name_normalized, ];
            }
            return parent::_by_fields_multi(
                self::BY_NAME_NORMALIZED,
                $keys_arr,
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get Country id_arr by an array of iso2s
         *
         * @param array $iso2_arr an array containing iso2s
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays of Country ids
         */
        public function by_iso2_multi(array $iso2_arr, array $order_by=null, $offset=null, $limit=null) {
            $keys_arr = [];
            foreach ($iso2_arr as $k => $iso2) {
                $keys_arr[$k] = [ 'iso2' => (string) $iso2, ];
            }
            return parent::_by_fields_multi(
                self::BY_ISO2,
                $keys_arr,
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get Country id_arr by an array of iso3s
         *
         * @param array $iso3_arr an array containing iso3s
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays of Country ids
         */
        public function by_iso3_multi(array $iso3_arr, array $order_by=null, $offset=null, $limit=null) {
            $keys_arr = [];
            foreach ($iso3_arr as $k => $iso3) {
                $keys_arr[$k] = [ 'iso3' => (string) $iso3, ];
            }
            return parent::_by_fields_multi(
                self::BY_ISO3,
                $keys_arr,
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get Country id_arr by an array of names
         *
         * @param array $name_arr an array containing names
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays of Country ids
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
         * Insert Country record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return country_model
         */
        public function insert(array $info) {

            // Insert record
            return parent::_insert($info);
        }

        /**
         * Insert multiple Country records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return country_collection
         */
        public function inserts(array $infos) {

            // Insert record
            return parent::_inserts($infos);
        }

        /**
         * Updates a Country record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param country_model $country record to be updated
         * @param array $info data to write to the record
         *
         * @return country_model updated model
         */
        public function update(country_model $country, array $info) {

            // Update record
            return parent::_update($country, $info);
        }

        /**
         * Delete a Country record
         *
         * @param country_model $country record to be deleted
         *
         * @return bool
         */
        public function delete(country_model $country) {

            // Delete record
            return parent::_delete($country);
        }

        /**
         * Delete multiple Country records
         *
         * @param country_collection $country_collection records to be deleted
         *
         * @return bool
         */
        public function deletes(country_collection $country_collection) {

            // Delete records
            return parent::_deletes($country_collection);
        }
    }
