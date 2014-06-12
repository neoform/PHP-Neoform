<?php

    namespace neoform\acl\resource;

    /**
     * Acl Resource DAO
     */
    class dao extends \neoform\entity\record\dao implements definition {

        const BY_PARENT      = 'by_parent';
        const BY_PARENT_NAME = 'by_parent_name';

        /**
         * $var array $field_bindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $field_bindings = [
            'id'        => self::TYPE_INTEGER,
            'parent_id' => self::TYPE_INTEGER,
            'name'      => self::TYPE_STRING,
        ];

        /**
         * $var array $referenced_entities list of fields (in this entity) and their related foreign entity
         *
         * @return array
         */
        protected $referenced_entities = [
            'parent_id' => 'acl\resource',
        ];

        // READS

        /**
         * Get Acl Resource ids by parent
         *
         * @param int $parent_id
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of Acl Resource ids
         */
        public function by_parent($parent_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_PARENT,
                [
                    'parent_id' => $parent_id === null ? null : (int) $parent_id,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get Acl Resource ids by parent and name
         *
         * @param int $parent_id
         * @param string $name
         *
         * @return array of Acl Resource ids
         */
        public function by_parent_name($parent_id, $name) {
            return parent::_by_fields(
                self::BY_PARENT_NAME,
                [
                    'parent_id' => $parent_id === null ? null : (int) $parent_id,
                    'name'      => (string) $name,
                ]
            );
        }

        /**
         * Get multiple sets of Acl Resource ids by acl_resource
         *
         * @param \neoform\acl\resource\collection|array $acl_resource_list
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays containing Acl Resource ids
         */
        public function by_parent_multi($acl_resource_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($acl_resource_list instanceof \neoform\acl\resource\collection) {
                foreach ($acl_resource_list as $k => $acl_resource) {
                    $keys[$k] = [
                        'parent_id' => $acl_resource->id === null ? null : (int) $acl_resource->id,
                    ];
                }
            } else {
                foreach ($acl_resource_list as $k => $acl_resource) {
                    $keys[$k] = [
                        'parent_id' => $acl_resource === null ? null : (int) $acl_resource,
                    ];
                }
            }
            return parent::_by_fields_multi(
                self::BY_PARENT,
                $keys,
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get Acl Resource id_arr by an array of parent and names
         *
         * @param array $parent_name_arr an array of arrays containing parent_ids and names
         *
         * @return array of arrays of Acl Resource ids
         */
        public function by_parent_name_multi(array $parent_name_arr) {
            $keys_arr = [];
            foreach ($parent_name_arr as $k => $parent_name) {
                $keys_arr[$k] = [
                    'parent_id' => (int) $parent_name['parent_id'],
                    'name'      => (string) $parent_name['name'],
                ];
            }
            return parent::_by_fields_multi(
                self::BY_PARENT_NAME,
                $keys_arr
            );
        }

        // WRITES

        /**
         * Insert Acl Resource record, created from an array of $info
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
         * Insert multiple Acl Resource records, created from an array of arrays of $info
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
         * Updates a Acl Resource record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param model $acl_resource record to be updated
         * @param array $info data to write to the record
         *
         * @return model updated model
         */
        public function update(model $acl_resource, array $info) {

            // Update record
            return parent::_update($acl_resource, $info);
        }

        /**
         * Delete a Acl Resource record
         *
         * @param model $acl_resource record to be deleted
         *
         * @return bool
         */
        public function delete(model $acl_resource) {

            // Delete record
            return parent::_delete($acl_resource);
        }

        /**
         * Delete multiple Acl Resource records
         *
         * @param collection $acl_resource_collection records to be deleted
         *
         * @return bool
         */
        public function delete_multi(collection $acl_resource_collection) {

            // Delete records
            return parent::_delete_multi($acl_resource_collection);
        }
    }
