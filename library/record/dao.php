<?php

    /**
     * record_dao Standard database access, each extended DAO class must have a corresponding table with a primary key
     *
     * REQUIRED: every extended class *must* have the following constants:
     *    string TABLE       the table name in the database
     *    string NAME        the name of this record type (shown to users)
     *    string MODEL       the name of the model class
     *    string COLLECTION  the name of the collection class
     *    string EXCEPTION   the name of the exception class
     *    bool AUTOINCREMENT is this table an auto-increment table
     *    string PRIMARY_KEY column name of the primary key for this table
     *    bool BINARY_PK     is the primary key a binary string
     */

    abstract class record_dao {

        protected $source_engine;
        protected $source_engine_pool_read;
        protected $source_engine_pool_write;

        protected $cache_engine;
        protected $cache_engine_pool_read;
        protected $cache_engine_pool_write;

        // Key name used for primary key lookups
        const BY_PK = 'by_pk';

        // Limit based lookups
        const LIMIT = 'limit';

        // Paginated based lookups
        const PAGINATED = 'paginated';

        // Counts
        const COUNT = 'count';

        public function __construct(array $config) {
            $this->source_engine            = $config['source_engine'];
            $this->source_engine_pool_read  = $config['source_engine_pool_read'];
            $this->source_engine_pool_write = $config['source_engine_pool_write'];
            $this->cache_engine             = $config['cache_engine'];
            $this->cache_engine_pool_read   = $config['cache_engine_pool_read'];
            $this->cache_engine_pool_write  = $config['cache_engine_pool_write'];
        }

        /**
         * Get the PDO binding of a given column
         *
         * @param string $field_name name of column in this entity
         *
         * @return integer
         */
        public function pdo_binding($field_name) {
            return $this->pdo_bindings[$field_name];
        }

        /**
         * Get the PDO bindings of all columns
         *
         * @return array
         */
        public function pdo_bindings() {
            return $this->pdo_bindings;
        }

        /**
         * Get a cached recordset
         *
         * @access protected
         * @static
         * @final
         * @param string   $key full cache key with namespace - it's recomended that record_dao::_build_key() is used to create this key
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
         */
        final protected function cache_batch_start() {
            cache_lib::pipeline_start(
                $this->cache_engine,
                $this->cache_engine_pool_write
            );
        }

        /**
         * Execute batched cache queries
         *
         * @return mixed result from batch execution
         */
        final protected function cache_batch_execute() {
            return cache_lib::pipeline_execute(
                $this->cache_engine,
                $this->cache_engine_pool_write
            );
        }

        /**
         * Delete a cached record
         *
         * @access protected
         * @static
         * @final
         * @param string|array $key full cache key with namespace - it's recomended that record_dao::_build_key() is used to create this key
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
         * @param string  $entity_name    optional - closure function that retreieves the recordset from its origin
         * @return string a cache key that is unqiue to the application
         */
        final public static function _build_key($cache_key_name, array $params=[], $entity_name=null) {
            // each key is namespaced with the name of the class
            $param_count = count($params);
            if ($param_count === 1) {
                return ($entity_name ?: static::ENTITY_NAME) . ":{$cache_key_name}:" . md5(reset($params));
            } else if ($param_count === 0) {
                return ($entity_name ?: static::ENTITY_NAME) . ":{$cache_key_name}:";
            } else {
                ksort($params);
                foreach ($params as & $param) {
                    $param = base64_encode($param);
                }
                return ($entity_name ?: static::ENTITY_NAME) . ":{$cache_key_name}:" . md5(json_encode(array_values($params)));
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
                static::ENTITY_NAME . ':' . self::BY_PK . ':' . (static::BINARY_PK ? md5($pk) : $pk),
                function() use ($pk, $self) {
                    $source_driver = "record_driver_{$self->source_engine}";
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

            if (! count($pks)) {
                return [];
            }

            $self = $this;

            return cache_lib::multi(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                $pks,
                function($pk) use ($self) {
                    return $self::ENTITY_NAME . ':' . $self::BY_PK . ':' . ($self::BINARY_PK ? md5($pk) : $pk);
                },
                function(array $pks) use ($self) {
                    $source_driver = "record_driver_{$self->source_engine}";
                    return $source_driver::by_pks($self, $pks);
                }
            );
        }

        /**
         * Get a list of PKs, with a limit, offset and order by - this function should NOT be used with non-persistent
         * cache engines. If the LIMIT cache key expires, it can cause cache corruption. Eg, do not use memcached with this
         *
         * @param integer $limit     max number of PKs to return
         * @param string  $order_by  field name
         * @param string  $direction ASC|DESC
         * @param string  $after_pk  A PK offset to be used (it's more efficient to use PK offsets than an SQL 'OFFSET')
         *
         * @return array of PKs
         * @throws model_exception
         */
        public function limit($limit, $order_by, $direction, $after_pk=null) {

            if (! static::USING_LIMIT) {
                $exception = static::ENTITY_NAME . '_exception';
                throw new $exception('Limit queries are not active in the ' . static::NAME . ' entity definition');
            }

            $self      = $this;
            $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

            $cache_key = self::_build_key(
                self::LIMIT . ":{$order_by}",
                [
                    (int) $limit,
                    $direction,
                    $after_pk !== null && static::BINARY_PK ? md5($after_pk) : $after_pk,
                ]
            );

            return cache_lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                $cache_key,
                function() use ($self, $cache_key, $limit, $order_by, $direction, $after_pk) {

                    // create a list entry to store all the LIMIT keys - we need to be able to destroy these
                    // cache entries when something in the list changes
                    cache_lib::list_add(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        $self::_build_key($self::LIMIT . '[]'),
                        $cache_key
                    );

                    // Pull content from source
                    $source_driver = "record_driver_{$self->source_engine}";
                    return $source_driver::limit(
                        $self,
                        (int) $limit,
                        $order_by,
                        $direction,
                        $after_pk
                    );
                }
            );
        }

        /**
         * Get a paginated list of entity PKs
         * This function does not use any caching, and it's not particularly efficient in the first place.
         * For performance reasons, you should always try using the record_dao::limit() function instead.
         * When using large offsets on big tables, mysql tends to grind to a halt.
         *
         * @param string  $order_by
         * @param string  $direction
         * @param integer $offset
         * @param integer $limit
         *
         * @return array
         * @throws model_exception
         */
        public function paginated($order_by, $direction, $offset, $limit) {

            if (! static::USING_PAGINATED) {
                $exception = static::ENTITY_NAME . '_exception';
                throw new $exception('Limit queries are not active in the ' . static::NAME . ' entity definition');
            }

            $self      = $this;
            $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

            $cache_key = self::_build_key(
                self::LIMIT . ":{$order_by}",
                [
                    (int) $offset,
                    (int) $limit,
                    $direction,
                ]
            );

            return cache_lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                $cache_key,
                function() use ($self, $cache_key, $limit, $offset, $order_by, $direction) {

                    // create a list entry to store all the LIMIT keys - we need to be able to destroy these
                    // cache entries when something in the list changes
                    cache_lib::list_add(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        $self::_build_key($self::PAGINATED . '[]'),
                        $cache_key
                    );

                    // Pull content from source
                    $source_driver = "record_driver_{$self->source_engine}";
                    return $source_driver::paginated(
                        $self,
                        $order_by,
                        $direction,
                        (int) $offset,
                        (int) $limit
                    );
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
                self::_build_key($cache_key_name, []),
                function() use ($self, $pk, $keys) {
                    $source_driver = "record_driver_{$self->source_engine}";
                    return $source_driver::all($self, $pk, $keys);
                }
            );
        }

        /**
         * Get a full count of all records in the table
         *
         * @return integer
         */
        public function count() {

            $self = $this;

            return cache_lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                static::ENTITY_NAME . ':' . self::COUNT,
                function() use ($self) {
                    $source_driver = "record_driver_{$self->source_engine}";
                    return $source_driver::count($self);
                }
            );
        }

        /**
         * Gets the primary keys of records that match the $keys
         *
         * @access protected
         * @static
         * @final
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $keys           array of table keys and their values being looked up in the table
         * @return array  pks of records from cache
         * @throws model_exception
         */
        final protected function _by_fields($cache_key_name, array $keys) {

            $pk   = static::PRIMARY_KEY;
            $self = $this;

            return cache_lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                self::_build_key($cache_key_name, $keys),
                function() use ($self, $keys, $pk) {
                    $source_driver = "record_driver_{$self->source_engine}";
                    return $source_driver::by_fields($self, $keys, $pk);
                }
            );
        }

        /**
         * Gets the pks of more than one set of key values
         *
         * @access protected
         * @static
         * @final
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $keys_arr       array of arrays of table keys and their values being looked up in the table - each sub-array must have matching keys
         * @return array  pks of records from cache
         * @throws model_exception
         */
        final protected function _by_fields_multi($cache_key_name, array $keys_arr) {

            $pk   = static::PRIMARY_KEY;
            $self = $this;

            return cache_lib::multi(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                $keys_arr,
                function($fields) use ($self, $cache_key_name) {
                    return record_dao::_build_key($cache_key_name, $fields, $self);
                },
                function(array $keys_arr) use ($self, $pk) {
                    $source_driver = "record_driver_{$self->source_engine}";
                    return $source_driver::by_fields_multi($self, $keys_arr, $pk);
                }
            );
        }


        /**
         * Gets records that match the $keys, rather than just getting the pk, this gets the columns specified by $select_fields
         *
         * @access protected
         * @static
         * @final
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $select_fields  array of table fields (table columns) to be selected
         * @param array   $keys           array of table keys and their values being looked up in the table
         * @return array  array of records from cache
         * @throws model_exception
         */
        final protected function _by_fields_select($cache_key_name, array $select_fields, array $keys) {

            $self = $this;

            return cache_lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                self::_build_key($cache_key_name, $keys),
                function() use ($self, $select_fields, $keys) {
                    $source_driver = "record_driver_{$self->source_engine}";
                    return $source_driver::by_fields_select($self, $select_fields, $keys);
                }
            );
        }

        /**
         * Inserts a record into the database
         *
         * @access protected
         * @static
         * @param array   $info         an associative array of into to be put into the database
         * @param boolean $replace      optional - user REPLACE INTO instead of INSERT INTO
         * @param boolean $return_model optional - return a model of the new record
         * @return record_model|true if $return_model is set to true, the model created from the info is returned
         * @throws model_exception
         */
        protected function _insert(array $info, $replace = false, $return_model = true) {

            $source_driver = "record_driver_{$this->source_engine}";
            $info          = $source_driver::insert(
                $this,
                $info,
                static::AUTOINCREMENT,
                $replace
            );

            $this->cache_batch_start();

            // In case a blank record was cached
            cache_lib::delete(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                static::ENTITY_NAME . ':' . self::BY_PK . ':' . (static::BINARY_PK ? md5($info[static::PRIMARY_KEY]) : $info[static::PRIMARY_KEY])
            );

            if (static::USING_COUNT) {
                cache_lib::delete(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    static::ENTITY_NAME . ':' . self::COUNT
                );
            }

            $this->cache_batch_execute();

            if (static::USING_LIMIT) {
                $this->_delete_limit_cache();
            }

            if (static::USING_PAGINATED) {
                $this->_delete_paginated_cache();
            }

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
         * @param array   $infos             an array of associative arrays of into to be put into the database, if this dao represents multiple tables, the info will be split up across the applicable tables.
         * @param boolean $keys_match        optional - if all the records being inserted have the same array keys this should be true. it is faster to insert all the records at the same time, but this can only be done if they all have the same keys.
         * @param boolean $replace           optional - user REPLACE INTO instead of INSERT INTO
         * @param boolean $return_collection optional - return a collection of models created
         * @return record_collection|true if $return_collection is true function returns a collection
         * @throws model_exception
         */
        protected function _inserts(array $infos, $keys_match = true, $replace = false, $return_collection = true) {

            $source_driver = "record_driver_{$this->source_engine}";
            $infos         = $source_driver::inserts(
                $this,
                $infos,
                $keys_match,
                static::AUTOINCREMENT,
                $replace
            );

            if ($return_collection) {
                $models = [];
            }
            $delete_keys = [];

            if ($keys_match) {
                if (static::AUTOINCREMENT || $return_collection) {
                    foreach ($infos as $k => $info) {
                        if ($return_collection) {
                            $models[$k] = new $model(null, $info);
                        }
                        $delete_keys[] = static::ENTITY_NAME . ':' . self::BY_PK . ':' . (static::BINARY_PK ? md5($info[static::PRIMARY_KEY]) : $info[static::PRIMARY_KEY]);
                    }
                } else {
                    foreach ($infos as $info) {
                        $delete_keys[] = static::ENTITY_NAME . ':' . self::BY_PK . ':' . (static::BINARY_PK ? md5($info[static::PRIMARY_KEY]) : $info[static::PRIMARY_KEY]);
                    }
                }
            } else {
                foreach ($infos as $k => $info) {
                    if ($return_collection) {
                        $models[$k] = new $model(null, $info);
                    }
                    $delete_keys[] = static::ENTITY_NAME . ':' . self::BY_PK . ':' . (static::BINARY_PK ? md5($info[static::PRIMARY_KEY]) : $info[static::PRIMARY_KEY]);
                }
            }

            $this->cache_batch_start();

            cache_lib::delete_multi(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                $delete_keys
            );

            if (static::USING_COUNT) {
                cache_lib::delete(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    static::ENTITY_NAME . ':' . self::COUNT
                );
            }

            $this->cache_batch_execute();

            if (static::USING_LIMIT) {
                $this->_delete_limit_cache();
            }

            if (static::USING_PAGINATED) {
                $this->_delete_paginated_cache();
            }

            if ($return_collection) {
                $collection = static::ENTITY_NAME . '_collection';
                return new $collection(null, $models);
            } else {
                return true;
            }
        }

        /**
         * Updates a record in the database
         *
         * @access protected
         * @static
         * @param record_model $model         the model that is to be updated
         * @param array        $info          the new info to be put into the model
         * @param boolean      $return_model  optional - return a model of the new record
         * @return record_model|true if $return_model is true, an updated model is returned
         * @throws model_exception
         */
        protected function _update(record_model $model, array $info, $return_model = true) {

            if (! $info) {
                return $return_model ? $model : false;
            }

            $pk = static::PRIMARY_KEY;

            $source_driver = "record_driver_{$this->source_engine}";
            $source_driver::update($this, static::PRIMARY_KEY, $model, $info);

            $this->cache_batch_start();

            cache_lib::delete(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                static::ENTITY_NAME . ':' . self::BY_PK . ':' . (static::BINARY_PK ? md5($model->$pk) : $model->$pk)
            );

            // if the primary key was changed, bust the cache for that new key too
            // technically the PK should never change though... that kinda defeats the purpose of a record PK...
            if (isset($info[$pk])) {
                cache_lib::delete(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    static::ENTITY_NAME . ':' . self::BY_PK . ':' . (static::BINARY_PK ? md5($info[$pk]) : $info[$pk])
                );
            }

            $this->cache_batch_execute();

            // Delete LIMIT cache based on the fields that were changed - this might not be all fields, we so don't
            // necessarily need to delete all LIMIT caches.
            if (static::USING_LIMIT) {
                self::_delete_limit_cache(array_keys($info));
            }

            if (static::USING_PAGINATED) {
                self::_delete_paginated_cache(array_keys($info));
            }

            if ($return_model) {
                $updated_model = clone $model;
                $updated_model->_update($info);
                return $updated_model;
            } else {
                return true;
            }
        }

        /**
         * Deletes a record from the database
         *
         * @access protected
         * @static
         * @param record_model $model the model that is to be deleted
         * @return boolean returns true on success
         * @throws model_exception
         */
        protected function _delete(record_model $model) {

            $pk = static::PRIMARY_KEY;

            $source_driver = "record_driver_{$this->source_engine}";
            $source_driver::delete($this, $pk, $model);

            $this->cache_batch_start();

            cache_lib::delete(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                static::ENTITY_NAME . ':' . self::BY_PK . ':' . (static::BINARY_PK ? md5($model->$pk) : $model->$pk)
            );

            if (static::USING_COUNT) {
                cache_lib::delete(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    static::ENTITY_NAME . ':' . self::COUNT
                );
            }

            $this->cache_batch_execute();

            if (static::USING_LIMIT) {
                $this->_delete_limit_cache();
            }

            if (static::USING_PAGINATED) {
                $this->_delete_paginated_cache();
            }

            return true;
        }

        /**
         * Deletes a record from the database
         *
         * @access protected
         * @static
         * @param record_collection $collection the collection of models that is to be deleted
         * @return boolean returns true on success
         * @throws model_exception
         */
        protected function _deletes(record_collection $collection) {

            if (! count($collection)) {
                return;
            }

            $source_driver = "record_driver_{$this->source_engine}";
            $source_driver::deletes($this, static::PRIMARY_KEY, $collection);

            $delete_cache_keys = [];
            foreach ($collection->field(static::PRIMARY_KEY) as $pk) {
                $delete_cache_keys[] = static::ENTITY_NAME . ':' . self::BY_PK . ':' . (static::BINARY_PK ? ':' . md5($pk) : ":{$pk}");
            }

            $this->cache_batch_start();

            cache_lib::delete_multi(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                $delete_cache_keys
            );

            if (static::USING_COUNT) {
                cache_lib::delete(
                    $this->cache_engine,
                    $this->cache_engine_pool_write,
                    static::ENTITY_NAME . ':' . self::COUNT
                );
            }

            $this->cache_batch_execute();

            if (static::USING_LIMIT) {
                $this->_delete_limit_cache();
            }

            if (static::USING_PAGINATED) {
                $this->_delete_paginated_cache();
            }

            return true;
        }

        /**
         * Delete LIMIT caches - with optional field limitation
         *
         * @param string|array|null $order_by_field
         */
        protected function _delete_limit_cache($order_by_field=null) {

            if ($order_by_field !== null) {
                if (is_array($order_by_field) && $order_by_field) {
                    $filter = [];
                    foreach ($order_by_field as $f) {
                        $filter[] = static::ENTITY_NAME . ':' . self::LIMIT . ":{$f}:";
                    }
                } else {
                    $filter = static::ENTITY_NAME . ':' . self::LIMIT . ":{$order_by_field}:";
                }
            } else {
                $filter = null;
            }

            $this->_delete_list_cache(static::LIMIT . '[]', $filter);
        }

        /**
         * Delete LIMIT caches - with optional field limitation
         *
         * @param string|array|null $order_by_field
         */
        protected function _delete_paginated_cache($order_by_field=null) {

            if ($order_by_field !== null) {
                if (is_array($order_by_field) && $order_by_field) {
                    $filter = [];
                    foreach ($order_by_field as $f) {
                        $filter[] = static::ENTITY_NAME . ':' . self::PAGINATED . ":{$f}:";
                    }
                } else {
                    $filter = static::ENTITY_NAME . ':' . self::PAGINATED . ":{$order_by_field}:";
                }
            } else {
                $filter = null;
            }

            $this->_delete_list_cache(static::PAGINATED . '[]', $filter);
        }

        /**
         * Delete limit caches - with optional filters
         *
         * Filters operate by finding all keys that start with the filter string.
         * Eg, Filter: "user:id:" would match the following keys: "user:id:4", "user:id:5" and delete them
         *     but "user:email:foo@foo.com" would not be matched. It's the equivalent to an SQL "LIKE '%user:id:%'" query
         *
         * @param string            $list_key
         * @param string|array|null $filter
         */
        protected function _delete_list_cache($list_key, $filter=null) {
            cache_lib::delete_cache_filter_list(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                static::_build_key($list_key),
                $filter
            );
        }
    }