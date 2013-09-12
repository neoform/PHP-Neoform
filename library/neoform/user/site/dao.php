<?php

    namespace neoform\user\site;

    /**
     * User Site link DAO
     */
    class dao extends \neoform\entity\link\dao implements definition {

        const BY_SITE      = 'by_site';
        const BY_SITE_USER = 'by_site_user';
        const BY_USER      = 'by_user';

        /**
         * $var array $field_bindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $field_bindings = [
            'user_id' => self::TYPE_INTEGER,
            'site_id' => self::TYPE_INTEGER,
        ];

        // READS

        /**
         * Get user_id by site_id
         *
         * @param int $site_id
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array result set containing user_id
         */
        public function by_site($site_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_SITE,
                [
                    'user_id',
                ],
                [
                    'site_id' => (int) $site_id,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get user_id and site_id by site_id and user_id
         *
         * @param int $site_id
         * @param int $user_id
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array result set containing user_id and site_id
         */
        public function by_site_user($site_id, $user_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_SITE_USER,
                [
                    'user_id',
                    'site_id',
                ],
                [
                    'site_id' => (int) $site_id,
                    'user_id' => (int) $user_id,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get site_id by user_id
         *
         * @param int $user_id
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array result set containing site_id
         */
        public function by_user($user_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_USER,
                [
                    'site_id',
                ],
                [
                    'user_id' => (int) $user_id,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get multiple sets of site_id by a collection of users
         *
         * @param \neoform\user\collection|array $user_list
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array of result sets containing site_id
         */
        public function by_user_multi($user_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($user_list instanceof \neoform\user\collection) {
                foreach ($user_list as $k => $user) {
                    $keys[$k] = [
                        'user_id' => (int) $user->id,
                    ];
                }

            } else {
                foreach ($user_list as $k => $user) {
                    $keys[$k] = [
                        'user_id' => (int) $user,
                    ];
                }

            }

            return parent::_by_fields_multi(
                self::BY_USER,
                [
                    'site_id',
                ],
                $keys,
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get multiple sets of user_id by a collection of sites
         *
         * @param \neoform\site\collection|array $site_list
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array of result sets containing user_id
         */
        public function by_site_multi($site_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($site_list instanceof \neoform\site\collection) {
                foreach ($site_list as $k => $site) {
                    $keys[$k] = [
                        'site_id' => (int) $site->id,
                    ];
                }

            } else {
                foreach ($site_list as $k => $site) {
                    $keys[$k] = [
                        'site_id' => (int) $site,
                    ];
                }

            }

            return parent::_by_fields_multi(
                self::BY_SITE,
                [
                    'user_id',
                ],
                $keys,
                $order_by,
                $offset,
                $limit
            );
        }

        // WRITES

        /**
         * Insert User Site link, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public function insert(array $info) {

            return parent::_insert($info);
        }

        /**
         * Insert multiple User Site links, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public function insert_multi(array $infos) {

            return parent::_insert_multi($infos);
        }

        /**
         * Update User Site link records based on $where inputs
         *
         * @param array $new_info the new link record data
         * @param array $where associative array, matching columns with values
         *
         * @return bool
         */
        public function update(array $new_info, array $where) {

            // Update link
            return parent::_update($new_info, $where);
        }

        /**
         * Delete multiple User Site link records based on an array of associative arrays
         *
         * @param array $keys keys match the column names
         *
         * @return bool
         */
        public function delete(array $keys) {

            // Delete link
            return parent::_delete($keys);
        }

        /**
         * Delete multiple sets of User Site link records based on an array of associative arrays
         *
         * @param array $keys_arr an array of arrays, keys match the column names
         *
         * @return bool
         */
        public function delete_multi(array $keys_arr) {

            // Delete links
            return parent::_delete_multi($keys_arr);
        }
    }
