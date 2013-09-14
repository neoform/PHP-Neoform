<?php

    namespace neoform\region;

    /**
     * Region DAO
     */
    class dao extends \neoform\entity\record\dao implements definition {

        const BY_COUNTRY                 = 'by_country';
        const BY_COUNTRY_NAME_NORMALIZED = 'by_country_name_normalized';
        const BY_ISO2                    = 'by_iso2';
        const BY_COUNTRY_NAME            = 'by_country_name';
        const BY_NAME_SOUNDEX            = 'by_name_soundex';

        /**
         * $var array $field_bindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $field_bindings = [
            'id'              => self::TYPE_INTEGER,
            'country_id'      => self::TYPE_INTEGER,
            'name'            => self::TYPE_STRING,
            'name_normalized' => self::TYPE_STRING,
            'name_soundex'    => self::TYPE_STRING,
            'iso2'            => self::TYPE_STRING,
            'longitude'       => self::TYPE_DECIMAL,
            'latitude'        => self::TYPE_DECIMAL,
        ];

        /**
         * $var array $referenced_entities list of fields (in this entity) and their related foreign entity
         *
         * @return array
         */
        protected $referenced_entities = [
            'country_id' => 'country',
        ];

        // READS

        /**
         * Get Region ids by name_soundex
         *
         * @param string $name_soundex
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of Region ids
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
         * Get Region ids by country
         *
         * @param int $country_id
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of Region ids
         */
        public function by_country($country_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_COUNTRY,
                [
                    'country_id' => (int) $country_id,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get Region ids by country and name_normalized
         *
         * @param int $country_id
         * @param string $name_normalized
         *
         * @return array of Region ids
         */
        public function by_country_name_normalized($country_id, $name_normalized) {
            return parent::_by_fields(
                self::BY_COUNTRY_NAME_NORMALIZED,
                [
                    'country_id'      => (int) $country_id,
                    'name_normalized' => (string) $name_normalized,
                ]
            );
        }

        /**
         * Get Region ids by iso2
         *
         * @param string $iso2
         *
         * @return array of Region ids
         */
        public function by_iso2($iso2) {
            return parent::_by_fields(
                self::BY_ISO2,
                [
                    'iso2' => (string) $iso2,
                ]
            );
        }

        /**
         * Get Region ids by country and name
         *
         * @param int $country_id
         * @param string $name
         *
         * @return array of Region ids
         */
        public function by_country_name($country_id, $name) {
            return parent::_by_fields(
                self::BY_COUNTRY_NAME,
                [
                    'country_id' => (int) $country_id,
                    'name'       => (string) $name,
                ]
            );
        }

        /**
         * Get multiple sets of Region ids by country
         *
         * @param \neoform\country\collection|array $country_list
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays containing Region ids
         */
        public function by_country_multi($country_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($country_list instanceof \neoform\country\collection) {
                foreach ($country_list as $k => $country) {
                    $keys[$k] = [
                        'country_id' => (int) $country->id,
                    ];
                }
            } else {
                foreach ($country_list as $k => $country) {
                    $keys[$k] = [
                        'country_id' => (int) $country,
                    ];
                }
            }
            return parent::_by_fields_multi(
                self::BY_COUNTRY,
                $keys,
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get Region id_arr by an array of name_soundexs
         *
         * @param array $name_soundex_arr an array containing name_soundexs
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays of Region ids
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

        /**
         * Get Region id_arr by an array of country and name_normalizeds
         *
         * @param array $country_name_normalized_arr an array of arrays containing country_ids and name_normalizeds
         *
         * @return array of arrays of Region ids
         */
        public function by_country_name_normalized_multi(array $country_name_normalized_arr) {
            $keys_arr = [];
            foreach ($country_name_normalized_arr as $k => $country_name_normalized) {
                $keys_arr[$k] = [
                    'country_id'      => (int) $country_name_normalized['country_id'],
                    'name_normalized' => (string) $country_name_normalized['name_normalized'],
                ];
            }
            return parent::_by_fields_multi(
                self::BY_COUNTRY_NAME_NORMALIZED,
                $keys_arr
            );
        }

        /**
         * Get Region id_arr by an array of iso2s
         *
         * @param array $iso2_arr an array containing iso2s
         *
         * @return array of arrays of Region ids
         */
        public function by_iso2_multi(array $iso2_arr) {
            $keys_arr = [];
            foreach ($iso2_arr as $k => $iso2) {
                $keys_arr[$k] = [ 'iso2' => (string) $iso2, ];
            }
            return parent::_by_fields_multi(
                self::BY_ISO2,
                $keys_arr
            );
        }

        /**
         * Get Region id_arr by an array of country and names
         *
         * @param array $country_name_arr an array of arrays containing country_ids and names
         *
         * @return array of arrays of Region ids
         */
        public function by_country_name_multi(array $country_name_arr) {
            $keys_arr = [];
            foreach ($country_name_arr as $k => $country_name) {
                $keys_arr[$k] = [
                    'country_id' => (int) $country_name['country_id'],
                    'name'       => (string) $country_name['name'],
                ];
            }
            return parent::_by_fields_multi(
                self::BY_COUNTRY_NAME,
                $keys_arr
            );
        }

        // WRITES

        /**
         * Insert Region record, created from an array of $info
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
         * Insert multiple Region records, created from an array of arrays of $info
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
         * Updates a Region record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param model $region record to be updated
         * @param array $info data to write to the record
         *
         * @return model updated model
         */
        public function update(model $region, array $info) {

            // Update record
            return parent::_update($region, $info);
        }

        /**
         * Delete a Region record
         *
         * @param model $region record to be deleted
         *
         * @return bool
         */
        public function delete(model $region) {

            // Delete record
            return parent::_delete($region);
        }

        /**
         * Delete multiple Region records
         *
         * @param collection $region_collection records to be deleted
         *
         * @return bool
         */
        public function delete_multi(collection $region_collection) {

            // Delete records
            return parent::_delete_multi($region_collection);
        }
    }
