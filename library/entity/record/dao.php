<?php

    /**
     * entity_record_dao Standard database access, each extended DAO class must have a corresponding table with a primary key
     *
     * DAO class allows an entity to be looked up the same way as a regular DAO record, however it has the additional
     * ability to limit and order the lists or PKs that get looked up. Because the cache must always be accurate/fresh
     * lists of meta-data must be stored for each result set in order to identify which result sets (in cache) to destroy
     * when a record's field is updated.
     *
     * Eg, if a user's email address has changed, we must destroy any list of users that were 'order by email address ASC'
     * since that list is possibly no longer accurate. Also, since the result sets use limit/offsets, there are often
     * more than on result set cached away, so *all* cached result sets that used email to be ordered, most be destroyed.
     */
    abstract class entity_record_dao extends entity_dao {

        protected $binary_pk;

        // Key name used for primary key lookups
        const RECORD = 'record';

        // All records
        const ALL = 'all';

        // Generic orderby/limit/offset (with no WHERE)
        const LIMIT = 'limit';

        /**
         * Construct
         *
         * @param array $config
         */
        public function __construct(array $config) {
            parent::__construct($config);
            $this->binary_pk = $this->field_bindings[static::PRIMARY_KEY] === parent::TYPE_BINARY;
        }

        /**
         * Build a cache key used by the cache_lib by combining the dao class name, the cache key and the variables found in the $params
         *
         * @access public
         * @static
         * @final
         * @param string       $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array        $order_by       optional - array of order bys
         * @param integer      $offset         what starting position to get records from
         * @param integer|null $limit          how many records to select
         * @param array        $params         optional - array of table keys and their values being looked up in the table
         * @return string a cache key that is unqiue to the application
         */
        final protected static function _build_key_limit($cache_key_name, array $order_by, $offset, $limit=null, array $params=[]) {
            ksort($order_by);

            // each key is namespaced with the name of the class, then the name of the function ($cache_key_name)
            $param_count = count($params);
            if ($param_count === 1) {
                return static::ENTITY_NAME . ":{$cache_key_name}:{$offset},{$limit}:" . md5(json_encode($order_by)) .
                       ':' . md5(reset($params));
            } else if ($param_count === 0) {
                return static::ENTITY_NAME . ":{$cache_key_name}:{$offset},{$limit}:" . md5(json_encode($order_by)) . ':';
            } else {
                ksort($params);
                foreach ($params as & $param) {
                    $param = base64_encode($param);
                }
                // Use only the array_values() and not the named array, since each $cache_key_name is unique per function
                return static::ENTITY_NAME . ":{$cache_key_name}:{$offset},{$limit}:" . md5(json_encode($order_by)) .
                       ':' . md5(json_encode(array_values($params)));
            }
        }

        /**
         * Pulls a single record's information from the database
         *
         * @param int $pk primary key of a record
         *
         * @return array cached record data
         * @throws entity_exception
         */
        public function record($pk) {
            return cache_lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                static::ENTITY_NAME . ':' . self::RECORD . ':' . ($this->binary_pk ? md5($pk) : $pk),
                function() use ($pk) {
                    $source_driver = "entity_record_driver_{$this->source_engine}";
                    return $source_driver::record($this, $this->source_engine_pool_read, $pk);
                }
            );
        }

        /**
         * Pulls a single record's information from the database
         *
         * @access public
         * @static
         * @param array   $pks primary key of a records
         * @return array  cached records data - with preserved key names from $pks.
         * @throws entity_exception
         */
        public function records(array $pks) {

            if (! $pks) {
                return [];
            }

            return cache_lib::multi(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                $pks,
                function($pk) {
                    return $this::ENTITY_NAME . ':' . $this::RECORD . ':' . ($this->binary_pk ? md5($pk) : $pk);
                },
                function(array $pks) {
                    $source_driver = "entity_record_driver_{$this->source_engine}";
                    return $source_driver::records($this, $this->source_engine_pool_read, $pks);
                }
            );
        }

        /**
         * Get list of PKs, ordered and limited
         *
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of User ids
         */
        public function limit(array $order_by=null, $offset=null, $limit=null) {
            return self::_by_fields(
                parent::LIMIT,
                [],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get list of PKs, ordered and limited
         *
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of User ids
         */
        public function limits(array $order_by=null, $offset=null, $limit=null) {
            return self::_by_fields_multi(
                parent::LIMIT,
                [],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Gets the full record(s) that match the $keys
         *
         * @access protected
         * @static
         * @final
         * @param array $keys array of table keys and their values being looked up in the table
         * @return array pks of records from cache
         * @throws entity_exception
         */
        public function all(array $keys=null) {

            if ($keys) {
                $this->bind_fields($keys);
            }

            return cache_lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                parent::_build_key(self::ALL),
                function() use ($keys) {
                    $source_driver = "entity_record_driver_{$this->source_engine}";
                    return $source_driver::all($this, $this->source_engine_pool_read, $this::PRIMARY_KEY, $keys);
                },
                function($cache_key) {
                    $this->_set_meta_cache_always($cache_key);
                }
            );
        }

        /**
         * Get a record count
         *
         * @param array $keys
         *
         * @return integer
         */
        public function count(array $keys=null) {

            if ($keys) {
                $this->bind_fields($keys);
            }

            return cache_lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                parent::_build_key(parent::COUNT, $keys ?: []),
                function() use ($keys) {
                    $source_driver = "entity_record_driver_{$this->source_engine}";
                    return $source_driver::count($this, $this->source_engine_pool_read, $keys);
                },
                function($cache_key) use ($keys) {
                    $this->_set_meta_cache_count($cache_key, $keys);
                }
            );
        }

        /**
         * Gets the primary keys of records that match the $keys
         *
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $keys           array of fields/values being looked up in the table
         * @param array   $order_by       array of fields to order by - key = field, val = order direction
         * @param integer $offset         records starting at what offset
         * @param integer $limit          max number of record to return
         *
         * @return mixed
         */
        final protected function _by_fields($cache_key_name, array $keys, array $order_by=null, $offset=null, $limit=null) {
            if ($order_by) {
                $limit  = (int) $limit;
                $offset = $offset === null ? null : (int) $offset;

                $cache_key = self::_build_key_limit(
                    $cache_key_name,
                    $order_by,
                    (int) $offset,
                    (int) $limit,
                    $keys
                );

                return cache_lib::single(
                    $this->cache_engine,
                    $this->cache_engine_pool_read,
                    $this->cache_engine_pool_write,
                    $cache_key,
                    function() use ($cache_key, $keys, $order_by, $offset, $limit) {
                        $source_driver = "entity_record_driver_{$this->source_engine}";
                        return $source_driver::by_fields_offset(
                            $this,
                            $this->source_engine_pool_read,
                            $keys,
                            $this::PRIMARY_KEY,
                            $order_by,
                            $offset,
                            $limit
                        );
                    },
                    function($cache_key) use ($keys, $order_by) {
                        $this->_set_meta_cache($cache_key, $keys, array_values($order_by));
                    }
                );
            } else {
                return cache_lib::single(
                    $this->cache_engine,
                    $this->cache_engine_pool_read,
                    $this->cache_engine_pool_write,
                    parent::_build_key($cache_key_name, $keys),
                    function() use ($keys) {
                        $source_driver = "entity_record_driver_{$this->source_engine}";
                        return $source_driver::by_fields(
                            $this,
                            $this->source_engine_pool_read,
                            $keys,
                            $this::PRIMARY_KEY
                        );
                    },
                    function($cache_key) use ($keys) {
                        $this->_set_meta_cache($cache_key, $keys);
                    }
                );
            }
        }

        /**
         * Gets the pks of more than one set of key values
         *
         * @access protected
         * @static
         * @final
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $keys_arr       array of arrays of table keys and their values being looked up in the table - each sub-array must have matching keys
         * @param array   $order_by       array of fields to order by - key = field, val = order direction
         * @param integer $offset         records starting at what offset
         * @param integer $limit          max number of record to return
         * @return array  pks of records from cache
         * @throws entity_exception
         */
        final protected function _by_fields_multi($cache_key_name, array $keys_arr, array $order_by=null, $offset=null, $limit=null) {
            if ($order_by) {
                $limit  = (int) $limit;
                $offset = $offset === null ? null : (int) $offset;

                return cache_lib::multi(
                    $this->cache_engine,
                    $this->cache_engine_pool_read,
                    $this->cache_engine_pool_write,
                    $keys_arr,
                    function($fields) use ($cache_key_name, $order_by, $offset, $limit) {
                        return $this::_build_key_limit(
                            $cache_key_name,
                            $order_by,
                            (int) $offset,
                            (int) $limit,
                            $fields
                        );
                    },
                    function(array $keys_arr) use ($order_by, $offset, $limit) {
                        $source_driver = "entity_record_driver_{$this->source_engine}";
                        return $source_driver::by_fields_offset_multi(
                            $this,
                            $this->source_engine_pool_read,
                            $keys_arr,
                            $this::PRIMARY_KEY,
                            $order_by,
                            $offset,
                            $limit
                        );
                    },
                    function(array $cache_keys) use ($order_by) {
                        $this->_set_meta_cache_multi($cache_keys, $order_by);
                    }
                );
            } else {
                return cache_lib::multi(
                    $this->cache_engine,
                    $this->cache_engine_pool_read,
                    $this->cache_engine_pool_write,
                    $keys_arr,
                    function($fields) use ($cache_key_name) {
                        return $this::_build_key($cache_key_name, $fields);
                    },
                    function(array $keys_arr) {
                        $source_driver = "entity_record_driver_{$this->source_engine}";
                        return $source_driver::by_fields_multi(
                            $this,
                            $this->source_engine_pool_read,
                            $keys_arr,
                            $this::PRIMARY_KEY
                        );
                    },
                    function(array $cache_keys) {
                        $this->_set_meta_cache_multi($cache_keys);
                    }
                );
            }
        }

        /**
         * Inserts a record into the database
         *
         * @access protected
         * @static
         * @param array   $info                   an associative array of into to be put into the database
         * @param boolean $replace                optional - user REPLACE INTO instead of INSERT INTO
         * @param boolean $return_model           optional - return a model of the new record
         * @param boolean $load_model_from_source optional - after insert, load data from source - this is needed if the DB changes values on insert (eg, timestamps)
         * @return entity_record_model|boolean    if $return_model is set to true, the model created from the info is returned
         * @throws entity_exception
         */
        protected function _insert(array $info, $replace=false, $return_model=true, $load_model_from_source=false) {

            $source_driver = "entity_record_driver_{$this->source_engine}";
            $info          = $source_driver::insert(
                $this,
                $this->source_engine_pool_write,
                $info,
                static::AUTOINCREMENT,
                $replace
            );

            if ($load_model_from_source) {
                // Use master to avoid race condition
                $info = $source_driver::record($this, $this->source_engine_pool_write, $info[static::PRIMARY_KEY]);
            }

            // In case a blank record was cached
            cache_lib::set(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                static::ENTITY_NAME . ':' . self::RECORD . ':' . ($this->binary_pk ? md5($info[static::PRIMARY_KEY]) : $info[static::PRIMARY_KEY]),
                $info
            );

            self::_delete_meta_cache(
                $info,
                null,
                [ static::ENTITY_NAME . ':' . parent::ALWAYS ]
            );

            if ($return_model) {
                $model = static::ENTITY_NAME . '_model';
                return new $model(null, $info);
            } else {
                return true;
            }
        }

        /**
         * Inserts multiple record into the database
         *
         * @access protected
         * @static
         * @param array   $infos                    an array of associative arrays of into to be put into the database, if this dao represents multiple tables, the info will be split up across the applicable tables.
         * @param boolean $keys_match               optional - if all the records being inserted have the same array keys this should be true. it is faster to insert all the records at the same time, but this can only be done if they all have the same keys.
         * @param boolean $replace                  optional - user REPLACE INTO instead of INSERT INTO
         * @param boolean $return_collection        optional - return a collection of models created
         * @param boolean $load_models_from_source  optional - after insert, load data from source - this is needed if the DB changes values on insert (eg, timestamps)
         * @return entity_record_collection|boolean if $return_collection is true function returns a collection
         * @throws entity_exception
         */
        protected function _inserts(array $infos, $keys_match=true, $replace=false, $return_collection=true,
                                    $load_models_from_source=false) {

            $source_driver = "entity_record_driver_{$this->source_engine}";
            $infos         = $source_driver::inserts(
                $this,
                $this->source_engine_pool_write,
                $infos,
                $keys_match,
                static::AUTOINCREMENT,
                $replace
            );

            if ($load_models_from_source) {
                $ids = [];
                foreach ($infos as $k => $info) {
                    $ids[$k] = $info[static::PRIMARY_KEY];
                }

                // Use master to avoid race condition
                $infos = $source_driver::records($this, $this->source_engine_pool_write, $ids);
            }

            $insert_cache_data = [];
            foreach ($infos as $info) {
                $insert_cache_data[static::ENTITY_NAME . ':' . self::RECORD . ':' .
                    ($this->binary_pk ? md5($info[static::PRIMARY_KEY]) : $info[static::PRIMARY_KEY])] = $info;
            }

            cache_lib::set_multi(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                $insert_cache_data
            );

            self::_delete_meta_cache_multi($infos, [ static::ENTITY_NAME . ':' . parent::ALWAYS ]);

            if ($load_models_from_source) {
                return $return_collection ? new $collection(null, $infos) : true;
            } else {
                if ($return_collection) {
                    $collection = static::ENTITY_NAME . '_collection';
                    return new $collection(null, $infos);
                } else {
                    return true;
                }
            }
        }

        /**
         * Updates a record in the database
         *
         * @access protected
         * @static
         * @param entity_record_model $model                    the model that is to be updated
         * @param array               $new_info                 the new info to be put into the model
         * @param boolean             $return_model             optional - return a model of the new record
         * @param boolean             $reload_model_from_source optional - after update, load data from source - this is needed if the DB changes values on update (eg, timestamps)
         * @return entity_record_model|bool                     if $return_model is true, an updated model is returned
         * @throws entity_exception
         */
        protected function _update(entity_record_model $model, array $new_info, $return_model=true, $reload_model_from_source=false) {

            if (! $new_info) {
                return $return_model ? $model : false;
            }

            /**
             * Filter out any fields that have no actually changed - no point in updating the record and destroying
             * cache if nothing actually changed
             */
            $old_info = $model->export();
            $new_info = array_diff($new_info, $old_info);

            if (! $new_info) {
                return $return_model ? $model : false;
            }

            $pk = static::PRIMARY_KEY;

            $source_driver = "entity_record_driver_{$this->source_engine}";
            $source_driver::update($this, $this->source_engine_pool_write, static::PRIMARY_KEY, $model, $new_info);

            /**
             * Reload model from source based on current (or newly updated) PK
             * We reload it in case there were any fields updated by an external source during the process (such as a timestamp)
             */
            if ($reload_model_from_source) {
                // Use master to avoid race condition
                $new_info = $source_driver::record(
                    $this,
                    $this->source_engine_pool_write,
                    array_key_exists($pk, $new_info) ? $new_info[$pk] : $model->$pk
                );
            }

            $this->cache_batch_start($this->cache_engine, $this->cache_engine_pool_write);

            /**
             * If the primary key was changed, bust the cache for that new key too
             * technically the PK should never change though... that kinda defeats the purpose of a record PK...
             */
            if (array_key_exists($pk, $new_info)) {
                // Set the cache record
                cache_lib::set(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    static::ENTITY_NAME . ':' . self::RECORD . ':' . ($this->binary_pk ? md5($new_info[$pk]) : $new_info[$pk]),
                    $new_info + $old_info
                );

                // Destroy the old key
                if ($this->cache_engine_pool_read !== $this->cache_engine_pool_write) {
                    cache_lib::expire(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        static::ENTITY_NAME . ':' . self::RECORD . ':' . ($this->binary_pk ? md5($model->$pk) : $model->$pk),
                        $this->cache_delete_expire_ttl
                    );
                } else {
                    cache_lib::delete(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        static::ENTITY_NAME . ':' . self::RECORD . ':' . ($this->binary_pk ? md5($model->$pk) : $model->$pk)
                    );
                }
            } else {
                // Update cache record
                cache_lib::set(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    static::ENTITY_NAME . ':' . self::RECORD . ':' . ($this->binary_pk ? md5($model->$pk) : $model->$pk),
                    $new_info + $old_info
                );
            }

            $this->cache_batch_execute($this->cache_engine, $this->cache_engine_pool_write);

            // Destroy cache based on the fields that were changed - do not wrap this function in a batch execution
            self::_delete_meta_cache(
                array_diff($new_info, $old_info),
                array_diff($old_info, $new_info),
                [ static::ENTITY_NAME . ':' . parent::ALWAYS ]
            );

            if ($return_model) {
                if ($reload_model_from_source) {
                    return new $model(null, $new_info);
                } else {
                    $updated_model = clone $model;
                    $updated_model->_update($new_info);
                    return $updated_model;
                }
            } else {
                return true;
            }
        }

        /**
         * Deletes a record from the database
         *
         * @access protected
         * @static
         * @param entity_record_model $model the model that is to be deleted
         * @return boolean returns true on success
         * @throws entity_exception
         */
        protected function _delete(entity_record_model $model) {

            $pk = static::PRIMARY_KEY;

            $source_driver = "entity_record_driver_{$this->source_engine}";
            $source_driver::delete($this, $this->source_engine_pool_write, $pk, $model);

            if ($this->cache_engine_pool_read !== $this->cache_engine_pool_write) {
                cache_lib::expire(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    static::ENTITY_NAME . ':' . self::RECORD . ':' . ($this->binary_pk ? md5($model->$pk) : $model->$pk),
                    $this->cache_delete_expire_ttl
                );
            } else {
                cache_lib::delete(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    static::ENTITY_NAME . ':' . self::RECORD . ':' . ($this->binary_pk ? md5($model->$pk) : $model->$pk)
                );
            }

            // Destroy cache based on table fields - do not wrap this function in a batch execution
            self::_delete_meta_cache(
                array_keys(static::field_bindings()),
                null,
                [ static::ENTITY_NAME . ':' . parent::ALWAYS ]
            );

            return true;
        }

        /**
         * Deletes a record from the database
         *
         * @access protected
         * @static
         * @param entity_record_collection $collection the collection of models that is to be deleted
         * @return boolean returns true on success
         * @throws entity_exception
         */
        protected function _deletes(entity_record_collection $collection) {

            if (! count($collection)) {
                return;
            }

            $source_driver = "entity_record_driver_{$this->source_engine}";
            $source_driver::deletes($this, $this->source_engine_pool_write, static::PRIMARY_KEY, $collection);

            $delete_cache_keys = [];
            foreach ($collection->field(static::PRIMARY_KEY) as $pk) {
                $delete_cache_keys[] = static::ENTITY_NAME . ':' . self::RECORD . ':' . ($this->binary_pk ? ':' . md5($pk) : ":{$pk}");
            }

            $this->cache_batch_start($this->cache_engine, $this->cache_engine_pool_write);

            if ($this->cache_engine_pool_read !== $this->cache_engine_pool_write) {
                cache_lib::expire_multi(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    $delete_cache_keys,
                    $this->cache_delete_expire_ttl
                );
            } else {
                cache_lib::delete_multi(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    $delete_cache_keys
                );
            }

            $this->cache_batch_execute($this->cache_engine, $this->cache_engine_pool_write);

            // Destroy cache based on table fields - do not wrap this function in a batch execution
            self::_delete_meta_cache(
                array_keys(static::field_bindings()),
                null,
                [ static::ENTITY_NAME . ':' . parent::ALWAYS ]
            );

            return true;
        }

        /**
         * Create the meta data (lists) to identify which cache keys to destroy when the record or field values have been changed
         *
         * @param string     $cache_key
         * @param array|null $fieldvals
         */
        final public function _set_meta_cache_count($cache_key, array $fieldvals=null) {

            /**
             * Build lists of keys for deletion - when it's time to delete/modify the record
             */
            if ($fieldvals) {
                $this->cache_batch_start($this->cache_engine, $this->cache_engine_pool_write);

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
                foreach ($fieldvals as $field => $value) {
                    // Create a list key for the field/value
                    $list_key = parent::_build_key_list($field, $value);

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
                        parent::_build_key_list($field),
                        $list_key
                    );
                }

                $this->cache_batch_execute($this->cache_engine, $this->cache_engine_pool_write);
            } else {
                // Add the $list_key key to field list key - if it doesn't already exist
                cache_lib::list_add(
                    $this->cache_list_engine,
                    $this->cache_list_engine_pool_write,
                    static::ENTITY_NAME . ':' . parent::ALWAYS,
                    $cache_key
                );
            }
        }

        /**
         * Add a cache key to destroy any time any field in any record (of this type) changes
         *
         * @param string $cache_key
         */
        final public function _set_meta_cache_always($cache_key) {
            cache_lib::list_add(
                $this->cache_list_engine,
                $this->cache_list_engine_pool_write,
                static::ENTITY_NAME . ':' . parent::ALWAYS,
                $cache_key
            );
        }
    }