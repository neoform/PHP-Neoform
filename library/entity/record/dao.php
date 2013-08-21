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
    abstract class entity_record_dao {

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

        // Key name used for primary key lookups
        const RECORD = 'record';

        // Counts
        const COUNT = 'count';

        // List key - Always clear these keys on every change
        const ALWAYS  = 'always';

        // List key - Limit lists
        const LIMIT  = 'limit';

        // Offset key - Offset related record PK lists
        const OFFSET = 'offset';

        /**
         * Construct
         *
         * @param array $config
         */
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
         * Get a cached recordset
         *
         * @access protected
         * @static
         * @final
         * @param string   $key full cache key with namespace - it's recomended that entity_record_dao::_build_key() is used to create this key
         * @param callable $get closure function that retreieves the recordset from its origin
         * @return array   the cached recordset
         */
        final protected function _single($key, $get) {
            return cache_lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                $key,
                $get
            );
        }

        /**
         * Start batched query pipeline
         *
         * @param string $engine
         * @param string $pool
         */
        final protected function cache_batch_start($engine, $pool) {
            cache_lib::pipeline_start(
                $engine,
                $pool
            );
        }

        /**
         * Execute batched cache queries
         *
         * @param string $engine
         * @param string $pool
         *
         * @return mixed result from batch execution
         */
        final protected function cache_batch_execute($engine, $pool) {
            return cache_lib::pipeline_execute(
                $engine,
                $pool
            );
        }

        /**
         * Delete a cached record
         *
         * @access protected
         * @static
         * @final
         * @param string|array $key full cache key with namespace - it's recomended that entity_record_dao::_build_key() is used to create this key
         */
        final protected function _cache_delete($key) {
            if (is_array($key)) {
                cache_lib::delete_multi(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    $key
                );
            } else {
                cache_lib::delete(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    $key
                );
            }
        }

        /**
         * Build a cache key used by the cache_lib by combining the dao class name, the cache key and the variables found in the $params
         *
         * @access public
         * @static
         * @final
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $params         optional - array of table keys and their values being looked up in the table
         * @return string a cache key that is unqiue to the application
         */
        final public static function _build_key($cache_key_name, array $params=[]) {
            // each key is namespaced with the name of the class, then the name of the function ($cache_key_name)
            $param_count = count($params);
            if ($param_count === 1) {
                return static::ENTITY_NAME . ":{$cache_key_name}:" . md5(reset($params));
            } else if ($param_count === 0) {
                return static::ENTITY_NAME . ":{$cache_key_name}:";
            } else {
                ksort($params);
                foreach ($params as & $param) {
                    $param = base64_encode($param);
                }
                // Use only the array_values() and not the named array, since each $cache_key_name is unique per function
                return static::ENTITY_NAME . ":{$cache_key_name}:" . md5(json_encode(array_values($params)));
            }
        }

        /**
         * Build a list cache key with an optional field value
         *
         * @param String $field_name  name of field/column
         * @param mixed  $field_value value of field/column
         *
         * @return string
         */
        final public static function _build_key_list($field_name, $field_value=null) {
            if ($field_value === null) {
                return static::ENTITY_NAME . ':' . self::LIMIT . "[{$field_name}]";
            } else {
                return static::ENTITY_NAME . ':' . self::LIMIT . "[{$field_name}]:" . md5($field_value);
            }
        }

        /**
         * Build a list cache key for ordered fields
         *
         * @param String $field_name name of field/column
         *
         * @return string
         */
        final public static function _build_key_order($field_name) {
            return static::ENTITY_NAME . ':' . self::LIMIT . ":order_by[{$field_name}]";
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
        final public static function _build_key_offset($cache_key_name, array $order_by, $offset, $limit=null, array $params=[]) {
            ksort($order_by);

            // each key is namespaced with the name of the class, then the name of the function ($cache_key_name)
            $param_count = count($params);
            if ($param_count === 1) {
                return static::ENTITY_NAME . ":{$cache_key_name}:{$offset},{$limit}:" . md5(json_encode($order_by)) . ':' . md5(reset($params));
            } else if ($param_count === 0) {
                return static::ENTITY_NAME . ":{$cache_key_name}:{$offset},{$limit}:" . md5(json_encode($order_by)) . ':';
            } else {
                ksort($params);
                foreach ($params as & $param) {
                    $param = base64_encode($param);
                }
                // Use only the array_values() and not the named array, since each $cache_key_name is unique per function
                return static::ENTITY_NAME . ":{$cache_key_name}:{$offset},{$limit}:" . md5(json_encode($order_by)) . ':' . md5(json_encode(array_values($params)));
            }
        }

        /**
         * Pulls a single record's information from the database
         *
         * @param int $pk primary key of a record
         *
         * @return array cached record data
         * @throws model_exception
         */
        public function by_pk($pk) {

            $self = $this;

            return cache_lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                static::ENTITY_NAME . ':' . self::RECORD . ':' . (static::BINARY_PK ? md5($pk) : $pk),
                function() use ($pk, $self) {
                    $source_driver = "entity_record_driver_{$self->source_engine}";
                    return $source_driver::by_pk($self, $self->source_engine_pool_read, $pk);
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
         * @throws model_exception
         */
        public function by_pks(array $pks) {

            if (! $pks) {
                return [];
            }

            $self = $this;

            return cache_lib::multi(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                $pks,
                function($pk) use ($self) {
                    return $self::ENTITY_NAME . ':' . $self::RECORD . ':' . ($self::BINARY_PK ? md5($pk) : $pk);
                },
                function(array $pks) use ($self) {
                    $source_driver = "entity_record_driver_{$self->source_engine}";
                    return $source_driver::by_pks($self, $self->source_engine_pool_read, $pks);
                }
            );
        }

        /**
         * Gets the full record(s) that match the $keys
         *
         * @access protected
         * @static
         * @final
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $keys           array of table keys and their values being looked up in the table
         * @return array  pks of records from cache
         * @throws model_exception
         */
        final protected function _all($cache_key_name, array $keys=null) {

            $pk   = static::PRIMARY_KEY;
            $self = $this;

            return cache_lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                self::_build_key($cache_key_name),
                function() use ($self, $pk, $keys) {
                    $source_driver = "entity_record_driver_{$self->source_engine}";
                    return $source_driver::all($self, $self->source_engine_pool_read, $pk, $keys);
                },
                function($cache_key) use ($self) {
                    $self->_set_always_cache_lists($cache_key);
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

            $self = $this;

            return cache_lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                self::_build_key(self::COUNT, $keys ?: []),
                function() use ($self, $keys) {
                    $source_driver = "entity_record_driver_{$self->source_engine}";
                    return $source_driver::count($self, $self->source_engine_pool_read, $keys);
                },
                function($cache_key) use ($self, $keys) {
                    $self->_set_count_cache_lists($cache_key, $keys);
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
        final protected function _by_fields($cache_key_name, array $keys, array $order_by, $offset, $limit) {

            $pk   = static::PRIMARY_KEY;
            $self = $this;

            if ($order_by) {
                $limit  = (int) $limit;
                $offset = $offset === null ? null : (int) $offset;

                $cache_key = self::_build_key_offset(
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
                    function() use ($self, $cache_key, $keys, $pk, $order_by, $offset, $limit) {
                        $source_driver = "entity_record_driver_{$self->source_engine}";
                        return $source_driver::by_fields_offset(
                            $self,
                            $self->source_engine_pool_read,
                            $keys,
                            $pk,
                            $order_by,
                            $offset,
                            $limit
                        );
                    },
                    function($cache_key) use ($self, $keys, $order_by) {
                        $self->_set_limit_cache_lists($cache_key, $keys, $order_by);
                    }
                );
            } else {
                return cache_lib::single(
                    $this->cache_engine,
                    $this->cache_engine_pool_read,
                    $this->cache_engine_pool_write,
                    self::_build_key($cache_key_name, $keys),
                    function() use ($self, $keys, $pk) {
                        $source_driver = "entity_record_driver_{$self->source_engine}";
                        return $source_driver::by_fields($self, $self->source_engine_pool_read, $keys, $pk);
                    },
                    function($cache_key) use ($self, $keys) {
                        $self->_set_limit_cache_lists($cache_key, $keys);
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
         * @throws model_exception
         */
        final protected function _by_fields_multi($cache_key_name, array $keys_arr, array $order_by, $offset, $limit) {

            $pk   = static::PRIMARY_KEY;
            $self = $this;

            if ($order_by) {
                $limit  = (int) $limit;
                $offset = $offset === null ? null : (int) $offset;

                return cache_lib::multi(
                    $this->cache_engine,
                    $this->cache_engine_pool_read,
                    $this->cache_engine_pool_write,
                    $keys_arr,
                    function($fields) use ($self, $cache_key_name, $order_by, $offset, $limit) {
                        return $self::_build_key_offset(
                            $cache_key_name,
                            $order_by,
                            (int) $offset,
                            (int) $limit,
                            $fields
                        );
                    },
                    function(array $keys_arr) use ($self, $pk, $order_by, $offset, $limit) {
                        $source_driver = "entity_record_driver_{$self->source_engine}";
                        return $source_driver::by_fields_offset_multi(
                            $self,
                            $self->source_engine_pool_read,
                            $keys_arr,
                            $pk,
                            $order_by,
                            $offset,
                            $limit
                        );
                    },
                    function(array $cache_keys) use ($self, $order_by) {
                        $self->_set_limit_cache_lists_multi($cache_keys, $order_by);
                    }
                );
            } else {
                return cache_lib::multi(
                    $this->cache_engine,
                    $this->cache_engine_pool_read,
                    $this->cache_engine_pool_write,
                    $keys_arr,
                    function($fields) use ($self, $cache_key_name) {
                        return $self::_build_key($cache_key_name, $fields);
                    },
                    function(array $keys_arr) use ($self, $pk) {
                        $source_driver = "entity_record_driver_{$self->source_engine}";
                        return $source_driver::by_fields_multi($self, $self->source_engine_pool_read, $keys_arr, $pk);
                    },
                    function(array $cache_keys) use ($self) {
                        $self->_set_limit_cache_lists_multi($cache_keys);
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
         * @throws model_exception
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
                $info = $source_driver::by_pk($this, $this->source_engine_pool_write, $info[static::PRIMARY_KEY]);
            }

            // In case a blank record was cached
            cache_lib::set(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                static::ENTITY_NAME . ':' . self::RECORD . ':' . (static::BINARY_PK ? md5($info[static::PRIMARY_KEY]) : $info[static::PRIMARY_KEY]),
                $info
            );

            self::_delete_limit_cache_by_fields($info);

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
         * @throws model_exception
         */
        protected function _inserts(array $infos, $keys_match=true, $replace=false, $return_collection=true, $load_models_from_source=false) {

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
                $infos = $source_driver::by_pks($this, $this->source_engine_pool_write, $ids);
            }

            $insert_cache_data = [];
            foreach ($infos as $info) {
                $insert_cache_data[static::ENTITY_NAME . ':' . self::RECORD . ':' . (static::BINARY_PK ? md5($info[static::PRIMARY_KEY]) : $info[static::PRIMARY_KEY])] = $info;
            }

            cache_lib::set_multi(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                $insert_cache_data
            );

            self::_delete_limit_cache_by_fields_multi($infos);

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
         * @throws model_exception
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
                $new_info = $source_driver::by_pk(
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
                    static::ENTITY_NAME . ':' . self::RECORD . ':' . (static::BINARY_PK ? md5($new_info[$pk]) : $new_info[$pk]),
                    $new_info + $old_info
                );

                // Destroy the old key
                if ($this->cache_engine_pool_read !== $this->cache_engine_pool_write) {
                    cache_lib::expire(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        static::ENTITY_NAME . ':' . self::RECORD . ':' . (static::BINARY_PK ? md5($model->$pk) : $model->$pk),
                        $this->cache_delete_expire_ttl
                    );
                } else {
                    cache_lib::delete(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        static::ENTITY_NAME . ':' . self::RECORD . ':' . (static::BINARY_PK ? md5($model->$pk) : $model->$pk)
                    );
                }
            } else {
                // Update cache record
                cache_lib::set(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    static::ENTITY_NAME . ':' . self::RECORD . ':' . (static::BINARY_PK ? md5($model->$pk) : $model->$pk),
                    $new_info + $old_info
                );
            }

            $this->cache_batch_execute($this->cache_engine, $this->cache_engine_pool_write);

            // Destroy cache based on the fields that were changed - do not wrap this function in a batch execution
            self::_delete_limit_cache_by_fields(
                array_diff($new_info, $old_info),
                array_diff($old_info, $new_info)
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
         * @throws model_exception
         */
        protected function _delete(entity_record_model $model) {

            $pk = static::PRIMARY_KEY;

            $source_driver = "entity_record_driver_{$this->source_engine}";
            $source_driver::delete($this, $this->source_engine_pool_write, $pk, $model);

            if ($this->cache_engine_pool_read !== $this->cache_engine_pool_write) {
                cache_lib::expire(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    static::ENTITY_NAME . ':' . self::RECORD . ':' . (static::BINARY_PK ? md5($model->$pk) : $model->$pk),
                    $this->cache_delete_expire_ttl
                );
            } else {
                cache_lib::delete(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    static::ENTITY_NAME . ':' . self::RECORD . ':' . (static::BINARY_PK ? md5($model->$pk) : $model->$pk)
                );
            }

            // Destroy cache based on table fields - do not wrap this function in a batch execution
            self::_delete_limit_cache_by_fields(array_keys(static::field_bindings()));

            return true;
        }

        /**
         * Deletes a record from the database
         *
         * @access protected
         * @static
         * @param entity_record_collection $collection the collection of models that is to be deleted
         * @return boolean returns true on success
         * @throws model_exception
         */
        protected function _deletes(entity_record_collection $collection) {

            if (! count($collection)) {
                return;
            }

            $source_driver = "entity_record_driver_{$this->source_engine}";
            $source_driver::deletes($this, $this->source_engine_pool_write, static::PRIMARY_KEY, $collection);

            $delete_cache_keys = [];
            foreach ($collection->field(static::PRIMARY_KEY) as $pk) {
                $delete_cache_keys[] = static::ENTITY_NAME . ':' . self::RECORD . ':' . (static::BINARY_PK ? ':' . md5($pk) : ":{$pk}");
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
            self::_delete_limit_cache_by_fields(array_keys(static::field_bindings()));

            return true;
        }

        /**
         * Create the meta data (lists) to identify which cache keys to destroy when the record or field values have been changed
         *
         * @param string                  $cache_key
         * @param array                   $keys
         * @param array                   $order_by
         */
        final protected function _set_limit_cache_lists($cache_key, array $keys, array $order_by=[]) {

            $this->cache_batch_start($this->cache_list_engine, $this->cache_list_engine_pool_write);

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
            foreach ($order_by as $field => $direction) {
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
            foreach (array_diff_key($keys, $order_by) as $field => $value) {
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

            $this->cache_batch_execute($this->cache_list_engine, $this->cache_list_engine_pool_write);
        }

        /**
         * Create the meta data (lists) to identify which cache keys to destroy when the record or field values have been changed
         *
         * @param array $cache_keys
         * @param array $order_by
         */
        final protected function _set_limit_cache_lists_multi(array $cache_keys, array $order_by=[]) {

            $this->cache_batch_start($this->cache_list_engine, $this->cache_list_engine_pool_write);

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
            foreach ($order_by as $field => $direction) {
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
            foreach ($cache_keys as $cache_key => $keys) {
                foreach (array_diff_key($keys, $order_by) as $field => $value) {
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

            $this->cache_batch_execute($this->cache_list_engine, $this->cache_list_engine_pool_write);
        }

        /**
         * Delete all cache keys and field/value and field order lists - by fields
         *
         * Do not wrap a batch execution around this function
         *
         * @param array $fields           list of fields/values - key = field
         * @param array $secondary_fields list of fields/values - key = field
         */
        final protected function _delete_limit_cache_by_fields(array $fields, array $secondary_fields=null) {
            $field_list_keys      = [];
            $list_keys            = [ static::ENTITY_NAME . ':' . self::ALWAYS, ];
            $list_items_to_remove = [];

            foreach ($fields as $field => $value) {

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
                if ($secondary_fields && array_key_exists($field, $secondary_fields)) {
                    $list_keys[] = $list_items_to_remove[$field_list_key][] = self::_build_key_list($field, $secondary_fields[$field]);
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

            $this->cache_batch_start($this->cache_engine, $this->cache_engine_pool_write);

            if ($this->cache_list_engine !== $this->cache_engine || $this->cache_list_engine_pool_write !== $this->cache_engine_pool_write) {
                $this->cache_batch_start($this->cache_list_engine, $this->cache_list_engine_pool_write);
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
                $this->cache_batch_execute($this->cache_list_engine, $this->cache_list_engine_pool_write);
            }

            $this->cache_batch_execute($this->cache_engine, $this->cache_engine_pool_write);
        }

        /**
         * Delete all cache keys and field/value and field order lists - by fields
         *
         * Do not wrap a batch execution around this function
         *
         * @param array $fields_arr array containing lists of fields/values - key = field
         */
        final protected function _delete_limit_cache_by_fields_multi(array $fields_arr) {
            $field_list_keys      = [];
            $list_keys            = [ static::ENTITY_NAME . ':' . self::ALWAYS, ];
            $list_items_to_remove = [];

            foreach ($fields_arr as $fields) {
                foreach ($fields as $field => $value) {

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

            $this->cache_batch_start($this->cache_engine, $this->cache_engine_pool_write);

            if ($this->cache_list_engine !== $this->cache_engine || $this->cache_list_engine_pool_write !== $this->cache_engine_pool_write) {
                $this->cache_batch_start($this->cache_list_engine, $this->cache_list_engine_pool_write);
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
                $this->cache_batch_execute($this->cache_list_engine, $this->cache_list_engine_pool_write);
            }

            $this->cache_batch_execute($this->cache_engine, $this->cache_engine_pool_write);
        }

        /**
         * Create the meta data (lists) to identify which cache keys to destroy when the record or field values have been changed
         *
         * @param string     $cache_key
         * @param array|null $keys
         */
        final public function _set_count_cache_lists($cache_key, array $keys=null) {

            /**
             * Build lists of keys for deletion - when it's time to delete/modify the record
             */
            if ($keys) {
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
                foreach ($keys as $field => $value) {
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

                $this->cache_batch_execute($this->cache_engine, $this->cache_engine_pool_write);
            } else {
                // Add the $list_key key to field list key - if it doesn't already exist
                cache_lib::list_add(
                    $this->cache_list_engine,
                    $this->cache_list_engine_pool_write,
                    static::ENTITY_NAME . ':' . self::ALWAYS,
                    $cache_key
                );
            }
        }

        /**
         * Add a cache key to destroy any time any field in any record (of this type) changes
         *
         * @param string $cache_key
         */
        public function _set_always_cache_lists($cache_key) {
            cache_lib::list_add(
                $this->cache_list_engine,
                $this->cache_list_engine_pool_write,
                static::ENTITY_NAME . ':' . self::ALWAYS,
                $cache_key
            );
        }
    }