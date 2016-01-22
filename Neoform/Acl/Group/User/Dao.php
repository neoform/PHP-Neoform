<?php

    namespace Neoform\Acl\Group\User;

    use Neoform;

    /**
     * Acl Group User link DAO
     */
    class Dao extends Neoform\Entity\Link\Dao {

        // Load entity details into the class
        use Details;

        const BY_ACL_GROUP      = 'by_acl_group';
        const BY_ACL_GROUP_USER = 'by_acl_group_user';
        const BY_USER           = 'by_user';

        /**
         * $var array $fieldBindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $fieldBindings = [
            'acl_group_id' => self::TYPE_INTEGER,
            'user_id'      => self::TYPE_INTEGER,
        ];

        /**
         * $var array $referencedEntities list of fields (in this entity) and their related foreign entity
         *
         * @return array
         */
        protected $referencedEntities = [
            'acl_group_id' => 'Neoform\Acl\Group',
            'user_id'      => 'Neoform\User',
        ];

        // READS

        /**
         * Get user_id by acl_group_id
         *
         * @param int $acl_group_id
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array result set containing user_id
         */
        public function by_acl_group($acl_group_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_byFields(
                self::BY_ACL_GROUP,
                [
                    'user_id',
                ],
                [
                    'acl_group_id' => (int) $acl_group_id,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get acl_group_id and user_id by acl_group_id and user_id
         *
         * @param int $acl_group_id
         * @param int $user_id
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array result set containing acl_group_id and user_id
         */
        public function by_acl_group_user($acl_group_id, $user_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_byFields(
                self::BY_ACL_GROUP_USER,
                [
                    'acl_group_id',
                    'user_id',
                ],
                [
                    'acl_group_id' => (int) $acl_group_id,
                    'user_id'      => (int) $user_id,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get acl_group_id by user_id
         *
         * @param int $user_id
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array result set containing acl_group_id
         */
        public function by_user($user_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_byFields(
                self::BY_USER,
                [
                    'acl_group_id',
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
         * Get multiple sets of user_id by a collection of acl_groups
         *
         * @param \Neoform\Acl\Group\Collection|array $acl_group_list
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array of result sets containing user_id
         */
        public function by_acl_group_multi($acl_group_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($acl_group_list instanceof \Neoform\Acl\Group\Collection) {
                foreach ($acl_group_list as $k => $acl_group) {
                    $keys[$k] = [
                        'acl_group_id' => (int) $acl_group->id,
                    ];
                }

            } else {
                foreach ($acl_group_list as $k => $acl_group) {
                    $keys[$k] = [
                        'acl_group_id' => (int) $acl_group,
                    ];
                }

            }

            return parent::_byFieldsMulti(
                self::BY_ACL_GROUP,
                [
                    'user_id',
                ],
                $keys,
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get multiple sets of acl_group_id by a collection of users
         *
         * @param \Neoform\User\Collection|array $user_list
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array of result sets containing acl_group_id
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
                    'acl_group_id',
                ],
                $keys,
                $order_by,
                $offset,
                $limit
            );
        }

        // WRITES

        /**
         * Insert Acl Group User link, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public function insert(array $info) {

            return parent::_insert($info);
        }

        /**
         * Insert multiple Acl Group User links, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public function insertMulti(array $infos) {

            return parent::_insertMulti($infos);
        }

        /**
         * Update Acl Group User link records based on $where inputs
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
         * Delete multiple Acl Group User link records based on an array of associative arrays
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
         * Delete multiple sets of Acl Group User link records based on an array of associative arrays
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
