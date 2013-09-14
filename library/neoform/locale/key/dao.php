<?php

    namespace neoform\locale\key;

    /**
     * Locale Key DAO
     */
    class dao extends \neoform\entity\record\dao implements definition {

        const BY_LOCALE    = 'by_locale';
        const BY_BODY      = 'by_body';
        const BY_NAMESPACE = 'by_namespace';

        /**
         * $var array $field_bindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $field_bindings = [
            'id'           => self::TYPE_INTEGER,
            'body'         => self::TYPE_STRING,
            'locale'       => self::TYPE_STRING,
            'namespace_id' => self::TYPE_INTEGER,
        ];

        /**
         * $var array $referenced_entities list of fields (in this entity) and their related foreign entity
         *
         * @return array
         */
        protected $referenced_entities = [
            'locale'       => 'locale',
            'namespace_id' => 'locale\namespace',
        ];

        // READS

        /**
         * Get Locale Key ids by locale
         *
         * @param string $locale
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of Locale Key ids
         */
        public function by_locale($locale, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_LOCALE,
                [
                    'locale' => (string) $locale,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get Locale Key ids by body
         *
         * @param string $body
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of Locale Key ids
         */
        public function by_body($body, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_BODY,
                [
                    'body' => (string) $body,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get Locale Key ids by namespace
         *
         * @param int $namespace_id
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of Locale Key ids
         */
        public function by_namespace($namespace_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_NAMESPACE,
                [
                    'namespace_id' => (int) $namespace_id,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get multiple sets of Locale Key ids by locale
         *
         * @param \neoform\locale\collection|array $locale_list
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays containing Locale Key ids
         */
        public function by_locale_multi($locale_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($locale_list instanceof \neoform\locale\collection) {
                foreach ($locale_list as $k => $locale) {
                    $keys[$k] = [
                        'locale' => (string) $locale->iso2,
                    ];
                }
            } else {
                foreach ($locale_list as $k => $locale) {
                    $keys[$k] = [
                        'locale' => (string) $locale,
                    ];
                }
            }
            return parent::_by_fields_multi(
                self::BY_LOCALE,
                $keys,
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get multiple sets of Locale Key ids by locale_namespace
         *
         * @param \neoform\locale\nspace\collection|array $locale_namespace_list
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays containing Locale Key ids
         */
        public function by_namespace_multi($locale_namespace_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($locale_namespace_list instanceof \neoform\locale\nspace\collection) {
                foreach ($locale_namespace_list as $k => $locale_namespace) {
                    $keys[$k] = [
                        'namespace_id' => (int) $locale_namespace->id,
                    ];
                }
            } else {
                foreach ($locale_namespace_list as $k => $locale_namespace) {
                    $keys[$k] = [
                        'namespace_id' => (int) $locale_namespace,
                    ];
                }
            }
            return parent::_by_fields_multi(
                self::BY_NAMESPACE,
                $keys,
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get Locale Key id_arr by an array of bodys
         *
         * @param array $body_arr an array containing bodys
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays of Locale Key ids
         */
        public function by_body_multi(array $body_arr, array $order_by=null, $offset=null, $limit=null) {
            $keys_arr = [];
            foreach ($body_arr as $k => $body) {
                $keys_arr[$k] = [ 'body' => (string) $body, ];
            }
            return parent::_by_fields_multi(
                self::BY_BODY,
                $keys_arr,
                $order_by,
                $offset,
                $limit
            );
        }

        // WRITES

        /**
         * Insert Locale Key record, created from an array of $info
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
         * Insert multiple Locale Key records, created from an array of arrays of $info
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
         * Updates a Locale Key record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param model $locale_key record to be updated
         * @param array $info data to write to the record
         *
         * @return model updated model
         */
        public function update(model $locale_key, array $info) {

            // Update record
            return parent::_update($locale_key, $info);
        }

        /**
         * Delete a Locale Key record
         *
         * @param model $locale_key record to be deleted
         *
         * @return bool
         */
        public function delete(model $locale_key) {

            // Delete record
            return parent::_delete($locale_key);
        }

        /**
         * Delete multiple Locale Key records
         *
         * @param collection $locale_key_collection records to be deleted
         *
         * @return bool
         */
        public function delete_multi(collection $locale_key_collection) {

            // Delete records
            return parent::_delete_multi($locale_key_collection);
        }
    }
