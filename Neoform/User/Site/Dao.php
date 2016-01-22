<?php

    namespace Neoform\User\Site;

    /**
     * User Site link DAO
     */
    class Dao extends \Neoform\Entity\Link\Dao {

        // Load entity details into the class
        use Details;

        const BY_SITE      = 'by_site';
        const BY_SITE_USER = 'by_site_user';
        const BY_USER      = 'by_user';

        /**
         * $var array $fieldBindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $fieldBindings = [
            'user_id' => self::TYPE_INTEGER,
            'site_id' => self::TYPE_INTEGER,
        ];

        /**
         * $var array $referencedEntities list of fields (in this entity) and their related foreign entity
         *
         * @return array
         */
        protected $referencedEntities = [
            'user_id' => 'Neoform\User',
            'site_id' => 'Neoform\Site',
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
            return parent::_byFields(
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
            return parent::_byFields(
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
            return parent::_byFields(
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
         * @param \Neoform\User\Collection|array $user_list
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array of result sets containing site_id
         */
        public function by_user_multi($user_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($user_list instanceof \Neoform\User\Collection) {
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

            return parent::_byFieldsMulti(
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
         * @param \Neoform\Site\Collection|array $site_list
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array of result sets containing user_id
         */
        public function by_site_multi($site_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($site_list instanceof \Neoform\Site\Collection) {
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

            return parent::_byFieldsMulti(
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
        public function insertMulti(array $infos) {

            return parent::_insertMulti($infos);
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
        public function deleteMulti(array $keys_arr) {

            // Delete links
            return parent::_deleteMulti($keys_arr);
        }
    }
