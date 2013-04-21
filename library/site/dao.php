<?php

    /**
     * Site DAO
     */
    class site_dao extends record_dao implements site_definition {

        const BY_ALL  = 'by_all';
        const BY_NAME = 'by_name';

        public static function castings() {
            return [
                'id'   => 'int',
                'name' => 'string',
            ];
        }

        /**
         * Caching engines used by this entity
         *
         * @return int
         */
        protected static function _cache_engines() {
            return cache_lib::MC;
        }

        // READS

        /**
         * Get Site ids by name
         *
         * @param string $name
         *
         * @return array of Site ids
         */
        public static function by_name($name) {
            return self::_by_fields(
                self::BY_NAME,
                [
                    'name' => (string) $name,
                ]
            );
        }

        /**
         * Get all data for all Site records
         *
         * @return array containing all Site records
         */
        public static function all() {
            return parent::_all(self::BY_ALL);
        }

        // WRITES

        /**
         * Insert Site record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return site_model
         */
        public static function insert(array $info) {
            $return = parent::_insert($info);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            // BY_NAME
            if (array_key_exists('name', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $info['name'],
                        ]
                    )
                );
            }

            return $return;
        }

        /**
         * Insert multiple Site records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return site_collection
         */
        public static function inserts(array $infos) {
            $return = parent::_inserts($infos);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            foreach ($infos as $info) {
                // BY_NAME
                if (array_key_exists('name', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_NAME,
                            [
                                'name' => (string) $info['name'],
                            ]
                        )
                    );
                }

            }

            return $return;
        }

        /**
         * Updates a Site record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param site_model $site record to be updated
         * @param array $info data to write to the record
         *
         * @return site_model updated model
         */
        public static function update(site_model $site, array $info) {
            $updated_model = parent::_update($site, $info);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            // BY_NAME
            if (array_key_exists('name', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $site->name,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $info['name'],
                        ]
                    )
                );
            }

            return $updated_model;
        }

        /**
         * Delete a Site record
         *
         * @param site_model $site record to be deleted
         *
         * @return bool
         */
        public static function delete(site_model $site) {
            $return = parent::_delete($site);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            // BY_NAME
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_NAME,
                    [
                        'name' => (string) $site->name,
                    ]
                )
            );

            return $return;
        }

        /**
         * Delete multiple Site records
         *
         * @param site_collection $site_collection records to be deleted
         *
         * @return bool
         */
        public static function deletes(site_collection $site_collection) {
            $return = parent::_deletes($site_collection);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            foreach ($site_collection as $site) {
                // BY_NAME
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $site->name,
                        ]
                    )
                );

            }

            return $return;
        }

    }
