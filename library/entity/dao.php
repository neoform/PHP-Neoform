<?php

    abstract class entity_dao {

        protected $source_engine;
        protected $source_engine_pool_read;
        protected $source_engine_pool_write;

        protected $cache_engine;
        protected $cache_engine_pool_read;
        protected $cache_engine_pool_write;

        protected $cache_list_engine;
        protected $cache_list_engine_pool_read;
        protected $cache_list_engine_pool_write;

        protected $cache_delete_expire_ttl;

        /**
         * Order By
         */
        const SORT_ASC  = 0;
        const SORT_DESC = 1;

        /**
         * Types
         */
        const TYPE_STRING  = 1;
        const TYPE_INTEGER = 2;
        const TYPE_BINARY  = 3;
        const TYPE_FLOAT   = 4;
        const TYPE_DECIMAL = 5;
        const TYPE_BOOL    = 6;

        // Counts
        const COUNT = 'count';

        // List key - Always clear these keys on every change
        const ALWAYS = 'always';

        // List key
        const META = 'meta';

        public function __construct(array $config) {
            $this->source_engine                = $config['source_engine'];
            $this->source_engine_pool_read      = $config['source_engine_pool_read'];
            $this->source_engine_pool_write     = $config['source_engine_pool_write'];

            $this->cache_engine                 = $config['cache_engine'];
            $this->cache_engine_pool_read       = $config['cache_engine_pool_read'];
            $this->cache_engine_pool_write      = $config['cache_engine_pool_write'];

            $this->cache_list_engine            = $config['cache_list_engine'];
            $this->cache_list_engine_pool_read  = $config['cache_list_engine_pool_read'];
            $this->cache_list_engine_pool_write = $config['cache_list_engine_pool_write'];

            $this->cache_delete_expire_ttl      = $config['cache_delete_expire_ttl'];
        }

        /**
         * Get the PDO binding of a given column
         *
         * @param string $field_name name of column in this entity
         *
         * @return integer
         */
        public function field_binding($field_name) {
            return $this->field_bindings[$field_name];
        }

        /**
         * Get the PDO bindings of all columns
         *
         * @return array
         */
        public function field_bindings() {
            return $this->field_bindings;
        }

        /**
         * Bind an field's values to its appropriate variable type
         *
         * @param string $field_name
         * @param mixed  $value
         *
         * @return mixed
         */
        protected function bind_field($field_name, $value) {
            switch ($this->field_bindings[$field_name]) {
                case self::TYPE_STRING:
                    return (string) $value;

                case self::TYPE_INTEGER:
                    return (int) $value;

                case self::TYPE_BINARY:
                    return (binary) $value;

                case self::TYPE_FLOAT:
                case self::TYPE_DECIMAL:
                    return (float) $value;

                case self::TYPE_BOOL:
                    return (bool) $value;
            }
        }

        /**
         * Bind an array's values to their appropriate variable types
         *
         * @param array $fields
         */
        protected function bind_fields(array &$fields) {
            foreach ($fields as $k => &$v) {
                switch ($this->field_bindings[$k]) {
                    case self::TYPE_STRING:
                        $v = (string) $v;
                        break;

                    case self::TYPE_INTEGER:
                        $v = (int) $v;
                        break;

                    case self::TYPE_BINARY:
                        $v = (binary) $v;
                        break;

                    case self::TYPE_FLOAT:
                    case self::TYPE_DECIMAL:
                        $v = (float) $v;
                        break;

                    case self::TYPE_BOOL:
                        $v = (bool) $v;
                        break;
                }
            }
        }

        /**
         * Build a cache key used by the cache_lib by combining the dao class name, the cache key and the variables
         * found in the $fieldvals
         *
         * @access public
         * @static
         * @final
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $fieldvals      optional - array of table keys and their values being looked up in the table
         * @return string a cache key that is unqiue to the application
         */
        final protected static function _build_key($cache_key_name, array $fieldvals=[]) {
            // each key is namespaced with the name of the class, then the name of the function ($cache_key_name)
            $param_count = count($fieldvals);
            if ($param_count === 1) {
                return static::ENTITY_NAME . ":{$cache_key_name}:" . md5(reset($fieldvals));
            } else if ($param_count === 0) {
                return static::ENTITY_NAME . ":{$cache_key_name}:";
            } else {
                ksort($fieldvals);
                foreach ($fieldvals as & $val) {
                    $val = base64_encode($val);
                }
                // Use only the array_values() and not the named array, since each $cache_key_name is unique per function
                return static::ENTITY_NAME . ":{$cache_key_name}:" . md5(json_encode(array_values($fieldvals)));
            }
        }

        /**
         * Build a list cache key with an optional field value
         *
         * @param String $field_name name of field/column
         * @param mixed  $fieldval   value of field/column
         *
         * @return string
         */
        final protected static function _build_key_list($field_name, $fieldval=null) {
            if ($fieldval === null) {
                return static::ENTITY_NAME . ':' . self::META . "[{$field_name}]";
            } else {
                return static::ENTITY_NAME . ':' . self::META . "[{$field_name}]:" . md5($fieldval);
            }
        }

        /**
         * Build a list cache key for ordered fields
         *
         * @param String $field_name name of field/column
         *
         * @return string
         */
        final protected static function _build_key_order($field_name) {
            return static::ENTITY_NAME . ':' . self::META . ":order_by[{$field_name}]";
        }

        /**
         * Get a cached link
         *
         * @access protected
         * @static
         * @final
         * @param string   $key full cache key with namespace - it's recomended that entity_record_dao::_build_key() is used to create this key
         * @param callable $get closure function that retreieves the recordset from its origin
         * @return array   the cached recordset
         */
        final protected function _single($key, callable $get) {
            return cache_lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                $key,
                $get
            );
        }

        /**
         * Delete a cached record
         *
         * @access protected
         * @static
         * @final
         * @param string   $key full cache key with namespace - it's recomended that entity_record_dao::_build_key() is used to create this key
         */
        final protected function _cache_delete($key) {
            cache_lib::delete(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                $key
            );
        }

        /**
         * Create the meta data (lists) to identify which cache keys to destroy when the record or field values have been changed
         *
         * @param string     $cache_key cache key for which we are storing meta data
         * @param array|null $fields    fields
         * @param array      $fieldvals   fields and values
         */
        final public function _set_meta_cache($cache_key, array $fieldvals, array $fields=[]) {

            cache_lib::pipeline_start($this->cache_list_engine, $this->cache_list_engine_pool_write);

            /**
             * Build lists of keys for deletion - when it's time to delete/modify the record
             */

            /**
             * Order by - goes first, since it's wider reaching, if there is overlap between $order_by fields
             * and $keys fields, we wont use those fields in $keys. (since they'll both contain the same cache
             * keys to destroy.
             *
             * An entry for each $fields field must be created (linking back to this set's $cache_key)
             */
            foreach ($fields as $field) {
                // Create list key for order by field
                $order_by_list_key = self::_build_key_order($field);

                // Store the cache key in $order_by_list_key list
                cache_lib::list_add(
                    $this->cache_list_engine,
                    $this->cache_list_engine_pool_write,
                    $order_by_list_key,
                    $cache_key
                );

                // Add the $order_by_list_key key to the field list key - if it doesn't already exist
                cache_lib::list_add(
                    $this->cache_list_engine,
                    $this->cache_list_engine_pool_write,
                    self::_build_key_list($field),
                    $order_by_list_key
                );
            }

            /**
             * Keys - An entry for each key and value must be created (linking back to this set's $cache_key)
             *
             * array_diff_key() is used to avoid doubling the deletion of keys when it's completely unnecessary.
             * If we're going to clear a field (because it's used in the order by), there's no point in also
             * clearing if because it's used as a field/value. (yes, I realize this is complicated and possibly confusing)
             *
             * Example: If you get records where id = 10 and you order by that same 'id' field, then every cached
             * result set that uses id for anything needs to be destroyed when any id changes in the table. Since
             * ordering by a field might be affected by any id, all resulting sets that involve that 'id' field,
             * must be cleared out.
             *
             * If foo_id = 10 and order by 'id' was used, then only cached result sets with foo_id = 10 would
             * need to be destroyed (along with all 'id' cached result sets).
             */
            foreach (array_diff_key($fieldvals, array_flip($fields)) as $field => $value) {
                // Create a list key for the field/value
                $list_key = self::_build_key_list($field, $value);

                // Store the cache key in the $list_key list
                cache_lib::list_add(
                    $this->cache_list_engine,
                    $this->cache_list_engine_pool_write,
                    $list_key,
                    $cache_key
                );

                // Add the $list_key key to field list key - if it doesn't already exist
                cache_lib::list_add(
                    $this->cache_list_engine,
                    $this->cache_list_engine_pool_write,
                    self::_build_key_list($field),
                    $list_key
                );
            }

            cache_lib::pipeline_execute($this->cache_list_engine, $this->cache_list_engine_pool_write);
        }

        /**
         * Create the meta data (lists) to identify which cache keys to destroy when the record or field values have been changed
         *
         * @param array $cache_keys
         * @param array $fields
         */
        final public function _set_meta_cache_multi(array $cache_keys, array $fields=[]) {

            cache_lib::pipeline_start($this->cache_list_engine, $this->cache_list_engine_pool_write);

            /**
             * Build lists of keys for deletion - when it's time to delete/modify the record
             */

            /**
             * Order by - goes first, since it's wider reaching, if there is overlap between $order_by fields
             * and $keys fields, we wont use those fields in $keys. (since they'll both contain the same cache
             * keys to destroy.
             *
             * An entry for each $order_by field must be created (linking back to this set's $cache_key)
             */
            foreach ($fields as $field) {
                // Create list key for order by field
                $order_by_list_key = self::_build_key_order($field);

                // Store the cache key in $order_by_list_key list
                cache_lib::list_add(
                    $this->cache_list_engine,
                    $this->cache_list_engine_pool_write,
                    $order_by_list_key,
                    array_keys($cache_keys)
                );

                // Add the $order_by_list_key key to the field list key - if it doesn't already exist
                cache_lib::list_add(
                    $this->cache_list_engine,
                    $this->cache_list_engine_pool_write,
                    self::_build_key_list($field),
                    $order_by_list_key
                );
            }

            /**
             * Keys - An entry for each key and value must be created (linking back to this set's $cache_key)
             *
             * array_diff_key() is used to avoid doubling the deletion of keys when it's completely unnecessary.
             * If we're going to clear a field (because it's used in the order by), there's no point in also
             * clearing if because it's used as a field/value. (yes, I realize this is complicated and possibly confusing)
             *
             * Example: If you get records where id = 10 and you order by that same 'id' field, then every cached
             * result set that uses id for anything needs to be destroyed when any id changes in the table. Since
             * ordering by a field might be affected by any id, all resulting sets that involve that 'id' field,
             * must be cleared out.
             *
             * If foo_id = 10 and order by 'id' was used, then only cached result sets with foo_id = 10 would
             * need to be destroyed (along with all 'id' cached result sets).
             */
            foreach ($cache_keys as $cache_key => $fieldvals) {
                if ($fieldvals) {
                    foreach (array_diff_key($fieldvals, array_flip($fields)) as $field => $value) {
                        // Create a list key for the field/value
                        $list_key = self::_build_key_list($field, $value);

                        // Store the cache key in the $list_key list
                        cache_lib::list_add(
                            $this->cache_list_engine,
                            $this->cache_list_engine_pool_write,
                            $list_key,
                            $cache_key
                        );

                        // Add the $list_key key to field list key - if it doesn't already exist
                        cache_lib::list_add(
                            $this->cache_list_engine,
                            $this->cache_list_engine_pool_write,
                            self::_build_key_list($field),
                            $list_key
                        );
                    }
                }
            }

            cache_lib::pipeline_execute($this->cache_list_engine, $this->cache_list_engine_pool_write);
        }

        /**
         * Delete all cache keys and field/value and field order lists - by fields
         *
         * Do not wrap a batch execution around this function
         *
         * @param array $fieldvals           list of fields/values
         * @param array $secondary_fieldvals list of fields/values
         * @param array $list_keys           list of keys to start off with (eg, key ALWAYS)
         */
        final protected function _delete_meta_cache(array $fieldvals, array $secondary_fieldvals=null, $list_keys=[]) {
            $field_list_keys      = [];
            $list_items_to_remove = [];

            foreach ($fieldvals as $field => $value) {

                // Which list does this field/value key belong to - we need to remove it from that list
                $field_list_key = self::_build_key_list($field);

                // Order by list key
                $list_keys[] = $list_items_to_remove[$field_list_key][] = self::_build_key_order($field);

                /**
                 * If the value is null, it means it's a parent $field_list_keys key (eg, limit[id])
                 * instead of a $list_keys (eg, limit[id]:5 or limit[id]:order_by)
                 */
                if ($value === null) {
                    $field_list_keys[] = $list_items_to_remove[$field_list_key][] = self::_build_key_list($field);
                } else {
                    $list_keys[] = $list_items_to_remove[$field_list_key][] = self::_build_key_list($field, $value);
                }

                // This gets used when an update took place - there is a new and old value
                if ($secondary_fieldvals && array_key_exists($field, $secondary_fieldvals)) {
                    $list_keys[] = $list_items_to_remove[$field_list_key][] = self::_build_key_list($field, $secondary_fieldvals[$field]);
                }
            }

            /**
             * If any $field_list_keys keys need deleting (eg, limit[id]), get all list keys from them (eg, limit[id]:5)
             */
            if ($field_list_keys) {
                $arr = cache_lib::list_get_union(
                    $this->cache_list_engine,
                    $this->cache_list_engine_pool_write,
                    $field_list_keys
                );

                if ($arr) {
                    $list_keys = array_unique(
                        array_merge($list_keys, $arr)
                    );
                }
            }

            /**
             * Get a union of all field/value list keys - combined
             * eg, limit[id]:555 + limit[id]:order_by + limit[email]:aaa@aaa.com + limit[email]:order_by
             */
            $cache_keys = cache_lib::list_get_union(
                $this->cache_list_engine,
                $this->cache_list_engine_pool_write,
                $list_keys
            );

            cache_lib::pipeline_start($this->cache_engine, $this->cache_engine_pool_write);

            if ($this->cache_list_engine !== $this->cache_engine || $this->cache_list_engine_pool_write !== $this->cache_engine_pool_write) {
                cache_lib::pipeline_start($this->cache_list_engine, $this->cache_list_engine_pool_write);
            }

            if ($this->cache_engine_pool_read !== $this->cache_engine_pool_write) {
                /**
                 * Expire all the keys selected above
                 */
                if ($this->cache_list_engine !== $this->cache_engine || $this->cache_list_engine_pool_write !== $this->cache_engine_pool_write) {
                    cache_lib::expire_multi(
                        $this->cache_list_engine,
                        $this->cache_list_engine_pool_write,
                        array_merge($list_keys, $field_list_keys),
                        $this->cache_delete_expire_ttl
                    );
                    cache_lib::expire_multi(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        $cache_keys,
                        $this->cache_delete_expire_ttl
                    );
                } else {
                    cache_lib::expire_multi(
                        $this->cache_list_engine,
                        $this->cache_list_engine_pool_write,
                        array_merge($cache_keys, $list_keys, $field_list_keys),
                        $this->cache_delete_expire_ttl
                    );
                }
            } else {
                /**
                 * Delete all the keys selected above
                 */
                if ($this->cache_list_engine !== $this->cache_engine || $this->cache_list_engine_pool_write !== $this->cache_engine_pool_write) {
                    cache_lib::delete_multi(
                        $this->cache_list_engine,
                        $this->cache_list_engine_pool_write,
                        array_merge($list_keys, $field_list_keys)
                    );
                    cache_lib::delete_multi(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        $cache_keys
                    );
                } else {
                    cache_lib::delete_multi(
                        $this->cache_list_engine,
                        $this->cache_list_engine_pool_write,
                        array_merge($cache_keys, $list_keys, $field_list_keys)
                    );
                }
            }

            /**
             * Since we just deleted $field_list_keys, we now remove those values from their parent lists
             * (Remove list field/value keys and order by keys from field lists)
             */
            foreach ($list_items_to_remove as $field_list_key => $remove_keys) {
                cache_lib::list_remove(
                    $this->cache_list_engine,
                    $this->cache_list_engine_pool_write,
                    $field_list_key,
                    array_unique($remove_keys)
                );
            }

            if ($this->cache_list_engine !== $this->cache_engine || $this->cache_list_engine_pool_write !== $this->cache_engine_pool_write) {
                cache_lib::pipeline_execute($this->cache_list_engine, $this->cache_list_engine_pool_write);
            }

            cache_lib::pipeline_execute($this->cache_engine, $this->cache_engine_pool_write);
        }

        /**
         * Delete all cache keys and field/value and field order lists - by fields
         *
         * Do not wrap a batch execution around this function
         *
         * @param array $fieldvals_arr array containing lists of fields/values
         * @param array $list_keys     list of keys to start off with (eg, key ALWAYS)
         */
        final protected function _delete_meta_cache_multi(array $fieldvals_arr, $list_keys=[]) {
            $field_list_keys      = [];
            $list_items_to_remove = [];

            foreach ($fieldvals_arr as $fieldvals) {
                foreach ($fieldvals as $field => $value) {

                    // @todo i'm fairly certain this can be made more efficient

                    // Which list does this field/value key belong to - we need to remove it from that list
                    $field_list_key = self::_build_key_list($field);

                    // Order by list key
                    $list_keys[] = $list_items_to_remove[$field_list_key][] = self::_build_key_order($field);

                    /**
                     * If the value is null, it means it's a parent $field_list_keys key (eg, limit[id])
                     * instead of a $list_keys (eg, limit[id]:5 or limit[id]:order_by)
                     */
                    if ($value === null) {
                        $field_list_keys[] = $list_items_to_remove[$field_list_key][] = self::_build_key_list($field);
                    } else {
                        $list_keys[] = $list_items_to_remove[$field_list_key][] = self::_build_key_list($field, $value);
                    }
                }
            }

            /**
             * If any $field_list_keys keys need deleting (eg, limit[id]), get all list keys from them (eg, limit[id]:5)
             */
            if ($field_list_keys) {

                $arr = cache_lib::list_get_union(
                    $this->cache_list_engine,
                    $this->cache_list_engine_pool_write,
                    $field_list_keys
                );

                if ($arr) {
                    $list_keys = array_unique(
                        array_merge($list_keys, $arr)
                    );
                }
            }

            /**
             * Get a union of all field/value list keys - combined
             * eg, limit[id]:555 + limit[id]:order_by + limit[email]:aaa@aaa.com + limit[email]:order_by
             */
            $cache_keys = cache_lib::list_get_union(
                $this->cache_list_engine,
                $this->cache_list_engine_pool_write,
                $list_keys
            );

            cache_lib::pipeline_start($this->cache_engine, $this->cache_engine_pool_write);

            if ($this->cache_list_engine !== $this->cache_engine || $this->cache_list_engine_pool_write !== $this->cache_engine_pool_write) {
                cache_lib::pipeline_start($this->cache_list_engine, $this->cache_list_engine_pool_write);
            }

            if ($this->cache_engine_pool_read !== $this->cache_engine_pool_write) {
                /**
                 * Expire all the keys selected above
                 */
                if ($this->cache_list_engine !== $this->cache_engine || $this->cache_list_engine_pool_write !== $this->cache_engine_pool_write) {
                    cache_lib::expire_multi(
                        $this->cache_list_engine,
                        $this->cache_list_engine_pool_write,
                        array_merge($list_keys, $field_list_keys),
                        $this->cache_delete_expire_ttl
                    );
                    cache_lib::expire_multi(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        $cache_keys,
                        $this->cache_delete_expire_ttl
                    );
                } else {
                    cache_lib::expire_multi(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        array_merge($cache_keys, $list_keys, $field_list_keys),
                        $this->cache_delete_expire_ttl
                    );
                }
            } else {
                /**
                 * Delete all the keys selected above
                 */
                if ($this->cache_list_engine !== $this->cache_engine || $this->cache_list_engine_pool_write !== $this->cache_engine_pool_write) {
                    cache_lib::delete_multi(
                        $this->cache_list_engine,
                        $this->cache_list_engine_pool_write,
                        array_merge($list_keys, $field_list_keys)
                    );
                    cache_lib::delete_multi(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        $cache_keys
                    );
                } else {
                    cache_lib::delete_multi(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        array_merge($cache_keys, $list_keys, $field_list_keys)
                    );
                }
            }

            if ($this->cache_list_engine !== $this->cache_engine || $this->cache_list_engine_pool_write !== $this->cache_engine_pool_write) {
                cache_lib::pipeline_execute($this->cache_list_engine, $this->cache_list_engine_pool_write);
            }

            cache_lib::pipeline_execute($this->cache_engine, $this->cache_engine_pool_write);
        }
    }