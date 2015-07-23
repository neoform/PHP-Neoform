<?php

    namespace Neoform\Acl\Role\Resource;

    /**
     * Acl Role Resource link DAO
     */
    class Dao extends \Neoform\Entity\Link\Dao implements Definition {

        const BY_ACL_ROLE              = 'by_acl_role';
        const BY_ACL_ROLE_ACL_RESOURCE = 'by_acl_role_acl_resource';
        const BY_ACL_RESOURCE          = 'by_acl_resource';

        /**
         * $var array $fieldBindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $fieldBindings = [
            'acl_role_id'     => self::TYPE_INTEGER,
            'acl_resource_id' => self::TYPE_INTEGER,
        ];

        /**
         * $var array $referencedEntities list of fields (in this entity) and their related foreign entity
         *
         * @return array
         */
        protected $referencedEntities = [
            'acl_role_id'     => 'Neoform\Acl\Role',
            'acl_resource_id' => 'Neoform\Acl\Resource',
        ];

        // READS

        /**
         * Get acl_resource_id by acl_role_id
         *
         * @param int $acl_role_id
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array result set containing acl_resource_id
         */
        public function by_acl_role($acl_role_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_byFields(
                self::BY_ACL_ROLE,
                [
                    'acl_resource_id',
                ],
                [
                    'acl_role_id' => (int) $acl_role_id,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get acl_role_id and acl_resource_id by acl_role_id and acl_resource_id
         *
         * @param int $acl_role_id
         * @param int $acl_resource_id
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array result set containing acl_role_id and acl_resource_id
         */
        public function by_acl_role_acl_resource($acl_role_id, $acl_resource_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_byFields(
                self::BY_ACL_ROLE_ACL_RESOURCE,
                [
                    'acl_role_id',
                    'acl_resource_id',
                ],
                [
                    'acl_role_id'     => (int) $acl_role_id,
                    'acl_resource_id' => (int) $acl_resource_id,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get acl_role_id by acl_resource_id
         *
         * @param int $acl_resource_id
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array result set containing acl_role_id
         */
        public function by_acl_resource($acl_resource_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_byFields(
                self::BY_ACL_RESOURCE,
                [
                    'acl_role_id',
                ],
                [
                    'acl_resource_id' => (int) $acl_resource_id,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get multiple sets of acl_resource_id by a collection of acl_roles
         *
         * @param \Neoform\Acl\Role\Collection|array $acl_role_list
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array of result sets containing acl_resource_id
         */
        public function by_acl_role_multi($acl_role_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($acl_role_list instanceof \Neoform\Acl\Role\Collection) {
                foreach ($acl_role_list as $k => $acl_role) {
                    $keys[$k] = [
                        'acl_role_id' => (int) $acl_role->id,
                    ];
                }

            } else {
                foreach ($acl_role_list as $k => $acl_role) {
                    $keys[$k] = [
                        'acl_role_id' => (int) $acl_role,
                    ];
                }

            }

            return parent::_byFieldsMulti(
                self::BY_ACL_ROLE,
                [
                    'acl_resource_id',
                ],
                $keys,
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get multiple sets of acl_role_id by a collection of acl_resources
         *
         * @param \Neoform\Acl\Resource\Collection|array $acl_resource_list
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array of result sets containing acl_role_id
         */
        public function by_acl_resource_multi($acl_resource_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($acl_resource_list instanceof \Neoform\Acl\Resource\Collection) {
                foreach ($acl_resource_list as $k => $acl_resource) {
                    $keys[$k] = [
                        'acl_resource_id' => (int) $acl_resource->id,
                    ];
                }

            } else {
                foreach ($acl_resource_list as $k => $acl_resource) {
                    $keys[$k] = [
                        'acl_resource_id' => (int) $acl_resource,
                    ];
                }

            }

            return parent::_byFieldsMulti(
                self::BY_ACL_RESOURCE,
                [
                    'acl_role_id',
                ],
                $keys,
                $order_by,
                $offset,
                $limit
            );
        }

        // WRITES

        /**
         * Insert Acl Role Resource link, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public function insert(array $info) {

            return parent::_insert($info);
        }

        /**
         * Insert multiple Acl Role Resource links, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public function insertMulti(array $infos) {

            return parent::_insertMulti($infos);
        }

        /**
         * Update Acl Role Resource link records based on $where inputs
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
         * Delete multiple Acl Role Resource link records based on an array of associative arrays
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
         * Delete multiple sets of Acl Role Resource link records based on an array of associative arrays
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
