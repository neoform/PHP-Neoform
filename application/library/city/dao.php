<?php

    /**
     * City DAO
     */
    class city_dao extends entity_record_dao implements city_definition {

        const BY_REGION                 = 'by_region';
        const BY_REGION_NAME_NORMALIZED = 'by_region_name_normalized';
        const BY_NAME_NORMALIZED        = 'by_name_normalized';
        const BY_NAME_SOUNDEX           = 'by_name_soundex';

        /**
         * $var array $field_bindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $field_bindings = [
            'id'              => self::TYPE_INTEGER,
            'region_id'       => self::TYPE_INTEGER,
            'name'            => self::TYPE_STRING,
            'name_normalized' => self::TYPE_STRING,
            'name_soundex'    => self::TYPE_STRING,
            'top'             => self::TYPE_STRING,
            'longitude'       => self::TYPE_FLOAT,
            'latitude'        => self::TYPE_FLOAT,
        ];

        // READS

        /**
         * Get City ids by region
         *
         * @param int $region_id
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of City ids
         */
        public function by_region($region_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_REGION,
                [
                    'region_id' => (int) $region_id,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get City ids by region and name_normalized
         *
         * @param int $region_id
         * @param string $name_normalized
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of City ids
         */
        public function by_region_name_normalized($region_id, $name_normalized, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_REGION_NAME_NORMALIZED,
                [
                    'region_id'       => (int) $region_id,
                    'name_normalized' => (string) $name_normalized,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get City ids by name_normalized
         *
         * @param string $name_normalized
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of City ids
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
         * Get City ids by name_soundex
         *
         * @param string $name_soundex
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of City ids
         */
        public function by_name_soundex($name_soundex, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_NAME_SOUNDEX,
                [
                    'name_soundex' => (string) $name_soundex,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get multiple sets of City ids by region
         *
         * @param region_collection|array $region_list
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays containing City ids
         */
        public function by_region_multi($region_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($region_list instanceof region_collection) {
                foreach ($region_list as $k => $region) {
                    $keys[$k] = [
                        'region_id' => (int) $region->id,
                    ];
                }
            } else {
                foreach ($region_list as $k => $region) {
                    $keys[$k] = [
                        'region_id' => (int) $region,
                    ];
                }
            }
            return parent::_by_fields_multi(
                self::BY_REGION,
                $keys,
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get City id_arr by an array of region and name_normalizeds
         *
         * @param array $region_name_normalized_arr an array of arrays containing region_ids and name_normalizeds
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays of City ids
         */
        public function by_region_name_normalized_multi(array $region_name_normalized_arr, array $order_by=null, $offset=null, $limit=null) {
            $keys_arr = [];
            foreach ($region_name_normalized_arr as $k => $region_name_normalized) {
                $keys_arr[$k] = [
                    'region_id'       => (int) $region_name_normalized['region_id'],
                    'name_normalized' => (string) $region_name_normalized['name_normalized'],
                ];
            }
            return parent::_by_fields_multi(
                self::BY_REGION_NAME_NORMALIZED,
                $keys_arr,
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get City id_arr by an array of name_normalizeds
         *
         * @param array $name_normalized_arr an array containing name_normalizeds
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays of City ids
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
         * Get City id_arr by an array of name_soundexs
         *
         * @param array $name_soundex_arr an array containing name_soundexs
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays of City ids
         */
        public function by_name_soundex_multi(array $name_soundex_arr, array $order_by=null, $offset=null, $limit=null) {
            $keys_arr = [];
            foreach ($name_soundex_arr as $k => $name_soundex) {
                $keys_arr[$k] = [ 'name_soundex' => (string) $name_soundex, ];
            }
            return parent::_by_fields_multi(
                self::BY_NAME_SOUNDEX,
                $keys_arr,
                $order_by,
                $offset,
                $limit
            );
        }

        // WRITES

        /**
         * Insert City record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return city_model
         */
        public function insert(array $info) {

            // Insert record
            return parent::_insert($info);
        }

        /**
         * Insert multiple City records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return city_collection
         */
        public function inserts(array $infos) {

            // Insert record
            return parent::_inserts($infos);
        }

        /**
         * Updates a City record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param city_model $city record to be updated
         * @param array $info data to write to the record
         *
         * @return city_model updated model
         */
        public function update(city_model $city, array $info) {

            // Update record
            return parent::_update($city, $info);
        }

        /**
         * Delete a City record
         *
         * @param city_model $city record to be deleted
         *
         * @return bool
         */
        public function delete(city_model $city) {

            // Delete record
            return parent::_delete($city);
        }

        /**
         * Delete multiple City records
         *
         * @param city_collection $city_collection records to be deleted
         *
         * @return bool
         */
        public function deletes(city_collection $city_collection) {

            // Delete records
            return parent::_deletes($city_collection);
        }
    }
