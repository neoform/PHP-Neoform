<?php

    namespace neoform\entity\record;

    use neoform\entity\exception;
    use neoform;

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
    abstract class dao extends neoform\entity\dao {

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
         * Build a cache key used by the neoform\cache\lib by combining the dao class name, the cache key and the variables found in the $params
         *
         * @param string       $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array        $order_by       optional - array of order bys
         * @param integer|null $offset         what starting position to get records from
         * @param integer|null $limit          how many records to select
         * @param array        $params         optional - array of table keys and their values being looked up in the table
         *
         * @return string a cache key that is unqiue to the application
         */
        final protected function _build_key_limit($cache_key_name, array $order_by, $offset=null, $limit=null, array $params=[]) {
            ksort($order_by);

            // each key is namespaced with the name of the class, then the name of the function ($cache_key_name)
            $param_count = count($params);
            if ($param_count === 1) {
                return static::CACHE_KEY . ":{$cache_key_name}:{$offset},{$limit}:" .
                    md5(json_encode($order_by), $this->cache_use_binary_keys) . ':' .
                    md5(reset($params), $this->cache_use_binary_keys);
            } else if ($param_count === 0) {
                return static::CACHE_KEY . ":{$cache_key_name}:{$offset},{$limit}:" .
                    md5(json_encode($order_by), $this->cache_use_binary_keys) . ':';
            } else {
                ksort($params);
                foreach ($params as & $param) {
                    $param = base64_encode($param);
                }
                // Use only the array_values() and not the named array, since each $cache_key_name is unique per function
                return static::CACHE_KEY . ":{$cache_key_name}:{$offset},{$limit}:" .
                    md5(json_encode($order_by), $this->cache_use_binary_keys) . ':' .
                    md5(json_encode(array_values($params)), $this->cache_use_binary_keys);
            }
        }

        /**
         * Pulls a single record's information from the database
         *
         * @param int $pk primary key of a record
         *
         * @return array cached record data
         * @throws exception
         */
        public function record($pk) {
            return neoform\cache\lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                $this->cache_engine_memory,
                static::CACHE_KEY . ':' . self::RECORD . ':' . ($this->binary_pk ? md5($pk, $this->cache_use_binary_keys) : $pk),
                function() use ($pk) {
                    $source_driver = "\\neoform\\entity\\record\\driver\\{$this->source_engine}";
                    return $source_driver::record($this, $this->source_engine_pool_read, $pk);
                }
            );
        }

        /**
         * Pulls a single record's information from the database
         *
         * @param array  $pks primary key of a records
         *
         * @return array cached records data - with preserved key names from $pks.
         * @throws exception
         */
        public function records(array $pks) {

            if (! $pks) {
                return [];
            }

            return neoform\cache\lib::multi(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                $this->cache_engine_memory,
                $pks,
                function($pk) {
                    return $this::CACHE_KEY . ':' . $this::RECORD . ':' . ($this->binary_pk ? md5($pk, $this->cache_use_binary_keys) : $pk);
                },
                function(array $pks) {
                    $source_driver = "\\neoform\\entity\\record\\driver\\{$this->source_engine}";
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
            return $this->_by_fields(
                self::LIMIT,
                [],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get multiple list of PKs, ordered and limited
         *
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of User ids
         */
        public function limit_multi(array $order_by=null, $offset=null, $limit=null) {
            return $this->_by_fields_multi(
                self::LIMIT,
                [],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Gets the full record(s) that match the $fieldvals
         *
         * @param array $fieldvals array of table keys and their values being looked up in the table
         *
         * @return array pks of records from cache
         * @throws exception
         */
        public function all(array $fieldvals=null) {

            if ($fieldvals) {
                $this->bind_fields($fieldvals);
            }

            return neoform\cache\lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                $this->cache_engine_memory,
                $this->_build_key(self::ALL),
                function() use ($fieldvals) {
                    $source_driver = "\\neoform\\entity\\record\\driver\\{$this->source_engine}";
                    return $source_driver::all($this, $this->source_engine_pool_read, $this::PRIMARY_KEY, $fieldvals);
                },
                function($cache_key) {
                    $this->_set_meta_cache($cache_key);
                }
            );
        }

        /**
         * Get a record count
         *
         * @param array|null $fieldvals
         *
         * @return integer
         */
        public function count(array $fieldvals=null) {

            if ($fieldvals) {
                $this->bind_fields($fieldvals);
            }

            return neoform\cache\lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                $this->cache_engine_memory,
                $this->_build_key(parent::COUNT, $fieldvals ?: []),
                function() use ($fieldvals) {
                    $source_driver = "\\neoform\\entity\\record\\driver\\{$this->source_engine}";
                    return $source_driver::count($this, $this->source_engine_pool_read, $fieldvals);
                },
                function($cache_key) use ($fieldvals) {
                    $this->_set_meta_cache($cache_key, $fieldvals);
                }
            );
        }

        /**
         * Get multiple record count
         *
         * @param array $fieldvals_arr
         *
         * @return array
         */
        public function count_multi(array $fieldvals_arr) {

            foreach ($fieldvals_arr as $fieldvals) {
                $this->bind_fields($fieldvals);
            }

            return neoform\cache\lib::multi(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                $this->cache_engine_memory,
                $fieldvals_arr,
                function($fieldvals) {
                    return $this->_build_key($this::COUNT, $fieldvals ?: []);
                },
                function(array $fieldvals_arr) {
                    $source_driver = "\\neoform\\entity\\record\\driver\\{$this->source_engine}";
                    return $source_driver::count_multi(
                        $this,
                        $this->source_engine_pool_read,
                        $fieldvals_arr
                    );
                },
                function(array $cache_keys, array $fieldvals_arr) {
                    // Can't use array_combine since the keys might not be in the same order (possibly)
                    $cache_keys_fieldvals = [];
                    foreach ($cache_keys as $k => $cache_key) {
                        $cache_keys_fieldvals[$cache_key] = $fieldvals_arr[$k];
                    }
                    $this->_set_meta_cache_multi($cache_keys_fieldvals);
                }
            );
        }

        /**
         * Gets the primary keys of records that match the $fieldvals
         *
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $fieldvals      array of fields/values being looked up in the table
         * @param array   $order_by       array of fields to order by - key = field, val = order direction
         * @param integer $offset         records starting at what offset
         * @param integer $limit          max number of record to return
         *
         * @return mixed
         */
        final protected function _by_fields($cache_key_name, array $fieldvals, array $order_by=null, $offset=null, $limit=null) {
            if ($order_by) {
                $limit  = $limit === null ? null : (int) $limit;
                $offset = $offset === null ? null : (int) $offset;

                $cache_key = $this->_build_key_limit(
                    $cache_key_name,
                    $order_by,
                    $offset,
                    $limit,
                    $fieldvals
                );

                return neoform\cache\lib::single(
                    $this->cache_engine,
                    $this->cache_engine_pool_read,
                    $this->cache_engine_pool_write,
                    $this->cache_engine_memory,
                    $cache_key,
                    function() use ($cache_key, $fieldvals, $order_by, $offset, $limit) {
                        $source_driver = "\\neoform\\entity\\record\\driver\\{$this->source_engine}";
                        return $source_driver::by_fields_offset(
                            $this,
                            $this->source_engine_pool_read,
                            $fieldvals,
                            $this::PRIMARY_KEY,
                            $order_by,
                            $offset,
                            $limit
                        );
                    },
                    function($cache_key, $pks) use ($fieldvals, $order_by) {

                        if (array_key_exists($this::PRIMARY_KEY, $fieldvals)) {
                            $fieldvals[$this::PRIMARY_KEY] = array_unique(array_merge(
                                is_array($fieldvals[$this::PRIMARY_KEY]) ? $fieldvals[$this::PRIMARY_KEY] : [ $fieldvals[$this::PRIMARY_KEY] ],
                                $pks
                            ));
                        } else {
                            $fieldvals[$this::PRIMARY_KEY] = $pks;
                        }

                        $this->_set_meta_cache($cache_key, $fieldvals, array_keys($order_by));
                    }
                );
            } else {
                return neoform\cache\lib::single(
                    $this->cache_engine,
                    $this->cache_engine_pool_read,
                    $this->cache_engine_pool_write,
                    $this->cache_engine_memory,
                    $this->_build_key($cache_key_name, $fieldvals),
                    function() use ($fieldvals) {
                        $source_driver = "\\neoform\\entity\\record\\driver\\{$this->source_engine}";
                        return $source_driver::by_fields(
                            $this,
                            $this->source_engine_pool_read,
                            $fieldvals,
                            $this::PRIMARY_KEY
                        );
                    },
                    function($cache_key, $pks) use ($fieldvals) {

                        $pk = $this::PRIMARY_KEY;

                        // The PKs found in this result set must also be put in meta cache to handle record deletion/updates
                        if (array_key_exists($pk, $fieldvals)) {
                            $fieldvals[$pk] = array_unique(array_merge(
                                is_array($fieldvals[$pk]) ? $fieldvals[$pk] : [ $fieldvals[$pk] ],
                                $pks
                            ));
                        } else {
                            $fieldvals[$pk] = $pks;
                        }

                        $this->_set_meta_cache($cache_key, $fieldvals);
                    }
                );
            }
        }

        /**
         * Gets the pks of more than one set of key values
         *
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $fieldvals_arr       array of arrays of table keys and their values being looked up in the table - each sub-array must have matching keys
         * @param array   $order_by       array of fields to order by - key = field, val = order direction
         * @param integer $offset         records starting at what offset
         * @param integer $limit          max number of record to return
         *
         * @return array  pks of records from cache
         * @throws exception
         */
        final protected function _by_fields_multi($cache_key_name, array $fieldvals_arr, array $order_by=null, $offset=null, $limit=null) {
            if ($order_by) {
                $limit  = $limit === null ? null : (int) $limit;
                $offset = $offset === null ? null : (int) $offset;

                return neoform\cache\lib::multi(
                    $this->cache_engine,
                    $this->cache_engine_pool_read,
                    $this->cache_engine_pool_write,
                    $this->cache_engine_memory,
                    $fieldvals_arr,
                    function($fieldvals) use ($cache_key_name, $order_by, $offset, $limit) {
                        return $this->_build_key_limit(
                            $cache_key_name,
                            $order_by,
                            $offset,
                            $limit,
                            $fieldvals
                        );
                    },
                    function(array $fieldvals_arr) use ($order_by, $offset, $limit) {
                        $source_driver = "\\neoform\\entity\\record\\driver\\{$this->source_engine}";
                        return $source_driver::by_fields_offset_multi(
                            $this,
                            $this->source_engine_pool_read,
                            $fieldvals_arr,
                            $this::PRIMARY_KEY,
                            $order_by,
                            $offset,
                            $limit
                        );
                    },
                    function(array $cache_keys, array $fieldvals_arr, array $pks_arr) use ($order_by) {

                        $pk = $this::PRIMARY_KEY;

                        $cache_keys_fieldvals = [];
                        foreach ($cache_keys as $k => $cache_key) {

                            // The PKs found in this result set must also be put in meta cache to handle record deletion/updates
                            $fieldvals = & $fieldvals_arr[$k];

                            if (array_key_exists($pk, $fieldvals)) {
                                $fieldvals[$pk] = array_unique(array_merge(
                                    is_array($fieldvals[$pk]) ? $fieldvals[$pk] : [ $fieldvals[$pk] ],
                                    $pks_arr[$k]
                                ));
                            } else {
                                $fieldvals[$pk] = $pks_arr[$k];
                            }

                            $cache_keys_fieldvals[$cache_key] = $fieldvals;
                        }

                        $this->_set_meta_cache_multi($cache_keys_fieldvals, array_keys($order_by));
                    }
                );
            } else {
                return neoform\cache\lib::multi(
                    $this->cache_engine,
                    $this->cache_engine_pool_read,
                    $this->cache_engine_pool_write,
                    $this->cache_engine_memory,
                    $fieldvals_arr,
                    function($fieldvals) use ($cache_key_name) {
                        return $this->_build_key($cache_key_name, $fieldvals);
                    },
                    function(array $fieldvals_arr) {
                        $source_driver = "\\neoform\\entity\\record\\driver\\{$this->source_engine}";
                        return $source_driver::by_fields_multi(
                            $this,
                            $this->source_engine_pool_read,
                            $fieldvals_arr,
                            $this::PRIMARY_KEY
                        );
                    },
                    function(array $cache_keys, array $fieldvals_arr, array $pks_arr) {
                        $pk = $this::PRIMARY_KEY;

                        $cache_keys_fieldvals = [];
                        foreach ($cache_keys as $k => $cache_key) {

                            // The PKs found in this result set must also be put in meta cache to handle record deletion/updates
                            $fieldvals = & $fieldvals_arr[$k];

                            if (array_key_exists($pk, $fieldvals)) {
                                $fieldvals[$pk] = array_unique(array_merge(
                                    is_array($fieldvals[$pk]) ? $fieldvals[$pk] : [ $fieldvals[$pk] ],
                                    $pks_arr[$k]
                                ));
                            } else {
                                $fieldvals[$pk] = $pks_arr[$k];
                            }

                            $cache_keys_fieldvals[$cache_key] = $fieldvals;
                        }

                        $this->_set_meta_cache_multi($cache_keys_fieldvals);
                    }
                );
            }
        }

        /**
         * Inserts a record into the database
         *
         * @param array   $info                   an associative array of into to be put into the database
         * @param boolean $replace                optional - user REPLACE INTO instead of INSERT INTO
         * @param boolean $return_model           optional - return a model of the new record
         * @param boolean $load_model_from_source optional - after insert, load data from source - this is needed if the DB changes values on insert (eg, timestamps)
         *
         * @return model|boolean if $return_model is set to true, the model created from the info is returned
         */
        protected function _insert(array $info, $replace=false, $return_model=true, $load_model_from_source=false) {

            $source_driver = "\\neoform\\entity\\record\\driver\\{$this->source_engine}";
            try {
                $info = $source_driver::insert(
                    $this,
                    $this->source_engine_pool_write,
                    $info,
                    static::AUTOINCREMENT,
                    $replace,
                    $this->source_engine_ttl
                );
            } catch (exception $e) {
                return false;
            }

            if ($load_model_from_source) {
                // Use master to avoid race condition
                $info = $source_driver::record($this, $this->source_engine_pool_write, $info[static::PRIMARY_KEY]);
            }

            // In case a blank record was cached
            neoform\cache\lib::set(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                $this->cache_engine_memory,
                static::CACHE_KEY . ':' . self::RECORD . ':' . ($this->binary_pk ? md5($info[static::PRIMARY_KEY], $this->cache_use_binary_keys) : $info[static::PRIMARY_KEY]),
                $info
            );

            $this->_delete_meta_cache($info);

            if ($return_model) {
                $model = '\\neoform\\' . static::ENTITY_NAME . '\\model';
                return new $model(null, $info);
            } else {
                return true;
            }
        }

        /**
         * Inserts multiple record into the database
         *
         * @param array   $infos                    an array of associative arrays of into to be put into the database, if this dao represents multiple tables, the info will be split up across the applicable tables.
         * @param boolean $keys_match               optional - if all the records being inserted have the same array keys this should be true. it is faster to insert all the records at the same time, but this can only be done if they all have the same keys.
         * @param boolean $replace                  optional - user REPLACE INTO instead of INSERT INTO
         * @param boolean $return_collection        optional - return a collection of models created
         * @param boolean $load_models_from_source  optional - after insert, load data from source - this is needed if the DB changes values on insert (eg, timestamps)
         *
         * @return collection|boolean if $return_collection is true function returns a collection
         * @throws exception
         */
        protected function _insert_multi(array $infos, $keys_match=true, $replace=false, $return_collection=true,
                                    $load_models_from_source=false) {

            $source_driver = "\\neoform\\entity\\record\\driver\\{$this->source_engine}";
            try {
                $infos = $source_driver::insert_multi(
                    $this,
                    $this->source_engine_pool_write,
                    $infos,
                    $keys_match,
                    static::AUTOINCREMENT,
                    $replace,
                    $this->source_engine_ttl
                );
            } catch (exception $e) {
                return false;
            }

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
                $insert_cache_data[static::CACHE_KEY . ':' . self::RECORD . ':' .
                    ($this->binary_pk ? md5($info[static::PRIMARY_KEY], $this->cache_use_binary_keys) : $info[static::PRIMARY_KEY])] = $info;
            }

            neoform\cache\lib::set_multi(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                $this->cache_engine_memory,
                $insert_cache_data
            );

            $this->_delete_meta_cache_multi($infos);

            if ($load_models_from_source) {
                $collection = '\\neoform\\' . static::ENTITY_NAME . '\\collection';
                return $return_collection ? new $collection(null, $infos) : true;
            } else {
                if ($return_collection) {
                    $collection = '\\neoform\\' . static::ENTITY_NAME . '\\collection';
                    return new $collection(null, $infos);
                } else {
                    return true;
                }
            }
        }

        /**
         * Updates a record in the database
         *
         * @param model   $model                    the model that is to be updated
         * @param array   $new_info                 the new info to be put into the model
         * @param boolean $return_model             optional - return a model of the new record
         * @param boolean $reload_model_from_source optional - after update, load data from source - this is needed if the DB changes values on update (eg, timestamps)
         *
         * @return model|bool                     if $return_model is true, an updated model is returned
         * @throws exception
         */
        protected function _update(model $model, array $new_info, $return_model=true,
                                   $reload_model_from_source=false) {

            if (! $new_info) {
                return $return_model ? $model : false;
            }

            /**
             * Filter out any fields that have not actually changed - no point in updating the record and destroying
             * cache if nothing actually changed
             */
            $old_info = $model->export();
            $new_info = array_intersect_key($new_info, $this->field_bindings);

            if (! $new_info) {
                return $return_model ? $model : false;
            }

            $pk = static::PRIMARY_KEY;

            $source_driver = "\\neoform\\entity\\record\\driver\\{$this->source_engine}";
            try {
                $source_driver::update(
                    $this,
                    $this->source_engine_pool_write,
                    static::PRIMARY_KEY,
                    $model,
                    $new_info,
                    $this->source_engine_ttl
                );
            } catch (exception $e) {
                return false;
            }

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

            neoform\cache\lib::pipeline_start($this->cache_engine, $this->cache_engine_pool_write);

            /**
             * If the primary key was changed, bust the cache for that new key too
             * technically the PK should never change though... that kinda defeats the purpose of a record PK...
             */
            if (array_key_exists($pk, $new_info)) {
                // Set the cache record
                neoform\cache\lib::set(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    $this->cache_engine_memory,
                    static::CACHE_KEY . ':' . self::RECORD . ':' . ($this->binary_pk ? md5($new_info[$pk], $this->cache_use_binary_keys) : $new_info[$pk]),
                    $new_info + $old_info
                );

                // Destroy the old key
                if ($this->cache_engine_pool_read !== $this->cache_engine_pool_write) {
                    neoform\cache\lib::expire(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        $this->cache_engine_memory,
                        static::CACHE_KEY . ':' . self::RECORD . ':' . ($this->binary_pk ? md5($model->$pk, $this->cache_use_binary_keys) : $model->$pk),
                        $this->cache_delete_expire_ttl
                    );
                } else {
                    neoform\cache\lib::delete(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        $this->cache_engine_memory,
                        static::CACHE_KEY . ':' . self::RECORD . ':' . ($this->binary_pk ? md5($model->$pk, $this->cache_use_binary_keys) : $model->$pk)
                    );
                }
            } else {
                // Update cache record
                neoform\cache\lib::set(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    $this->cache_engine_memory,
                    static::CACHE_KEY . ':' . self::RECORD . ':' . ($this->binary_pk ? md5($model->$pk, $this->cache_use_binary_keys) : $model->$pk),
                    $new_info + $old_info
                );
            }

            neoform\cache\lib::pipeline_execute($this->cache_engine, $this->cache_engine_pool_write);

            // Destroy cache based on the fields that were changed - do not wrap this function in a batch execution
            $this->_delete_meta_cache(
                $this->array_differences(
                    $new_info,
                    $old_info
                )
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
         * @param model $model the model that is to be deleted
         *
         * @return boolean returns true on success
         * @throws exception
         */
        protected function _delete(model $model) {

            $pk = static::PRIMARY_KEY;

            $source_driver = "\\neoform\\entity\\record\\driver\\{$this->source_engine}";
            try {
                $source_driver::delete($this, $this->source_engine_pool_write, $pk, $model);
            } catch (exception $e) {
                return false;
            }

            if ($this->cache_engine_pool_read !== $this->cache_engine_pool_write) {
                neoform\cache\lib::expire(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    $this->cache_engine_memory,
                    static::CACHE_KEY . ':' . self::RECORD . ':' . ($this->binary_pk ? md5($model->$pk, $this->cache_use_binary_keys) : $model->$pk),
                    $this->cache_delete_expire_ttl
                );
            } else {
                neoform\cache\lib::delete(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    $this->cache_engine_memory,
                    static::CACHE_KEY . ':' . self::RECORD . ':' . ($this->binary_pk ? md5($model->$pk, $this->cache_use_binary_keys) : $model->$pk)
                );
            }

            // Destroy cache based on table fieldvals - do not wrap this function in a batch execution
            $this->_delete_meta_cache($model->export());

            return true;
        }

        /**
         * Deletes a record from the database
         *
         * @param collection $collection the collection of models that is to be deleted
         *
         * @return boolean returns true on success
         * @throws exception
         */
        protected function _delete_multi(collection $collection) {

            if (! count($collection)) {
                return;
            }

            $source_driver = "\\neoform\\entity\\record\\driver\\{$this->source_engine}";
            try {
                $source_driver::delete_multi(
                    $this,
                    $this->source_engine_pool_write,
                    static::PRIMARY_KEY,
                    $collection
                );
            } catch (exception $e) {
                return false;
            }

            $delete_cache_keys = [];
            foreach ($collection->field(static::PRIMARY_KEY) as $pk) {
                $delete_cache_keys[] = static::CACHE_KEY . ':' . self::RECORD . ':' . ($this->binary_pk ? ':' . md5($pk, $this->cache_use_binary_keys) : ":{$pk}");
            }

            neoform\cache\lib::pipeline_start($this->cache_engine, $this->cache_engine_pool_write);

            if ($this->cache_engine_pool_read !== $this->cache_engine_pool_write) {
                neoform\cache\lib::expire_multi(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    $this->cache_engine_memory,
                    $delete_cache_keys,
                    $this->cache_delete_expire_ttl
                );
            } else {
                neoform\cache\lib::delete_multi(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    $this->cache_engine_memory,
                    $delete_cache_keys
                );
            }

            neoform\cache\lib::pipeline_execute($this->cache_engine, $this->cache_engine_pool_write);

            // Destroy cache based on table fieldvals - do not wrap this function in a batch execution
            $collection_data           = $collection->export();
            $collection_data_organized = [];
            foreach (array_keys(reset($collection_data)) as $field) {
                $collection_data_organized[$field] = array_column($collection_data, $field);
            }

            $this->_delete_meta_cache($collection_data_organized);

            return true;
        }
    }
