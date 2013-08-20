<?php

    /**
     * entity_record_dao Standard database access, each extended DAO class must have a corresponding table with a primary key
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

    abstract class entity_record_dao {

        protected $source_engine;
        protected $source_engine_pool_read;
        protected $source_engine_pool_write;

        protected $cache_engine;
        protected $cache_engine_pool_read;
        protected $cache_engine_pool_write;

        protected $cache_delete_expire_ttl;

        // Key name used for primary key lookups
        const RECORD = 'record';

        // Counts
        const COUNT = 'count';

        public function __construct(array $config) {
            $this->source_engine                = $config['source_engine'];
            $this->source_engine_pool_read      = $config['source_engine_pool_read'];
            $this->source_engine_pool_write     = $config['source_engine_pool_write'];

            $this->cache_engine                 = $config['cache_engine'];
            $this->cache_engine_pool_read       = $config['cache_engine_pool_read'];
            $this->cache_engine_pool_write      = $config['cache_engine_pool_write'];

            $this->cache_delete_expire_ttl      = $config['cache_delete_expire_ttl'];
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
         * @param string  $entity_name    optional - closure function that retreieves the recordset from its origin
         * @return string a cache key that is unqiue to the application
         */
        final public static function _build_key($cache_key_name, array $params=[], $entity_name=null) {
            // each key is namespaced with the name of the class, then the name of the function ($cache_key_name)
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
                // Use only the array_values() and not the named array, since each $cache_key_name is unique per function
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
                self::_build_key($cache_key_name, []),
                function() use ($self, $pk, $keys) {
                    $source_driver = "entity_record_driver_{$self->source_engine}";
                    return $source_driver::all($self, $self->source_engine_pool_read, $pk, $keys);
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
                    $source_driver = "entity_record_driver_{$self->source_engine}";
                    return $source_driver::count($self, $self->source_engine_pool_read);
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
                    $source_driver = "entity_record_driver_{$self->source_engine}";
                    return $source_driver::by_fields($self, $self->source_engine_pool_read, $keys, $pk);
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
                    return entity_record_dao::_build_key($cache_key_name, $fields, $self::ENTITY_NAME);
                },
                function(array $keys_arr) use ($self, $pk) {
                    $source_driver = "entity_record_driver_{$self->source_engine}";
                    return $source_driver::by_fields_multi($self, $self->source_engine_pool_read, $keys_arr, $pk);
                }
            );
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
        protected function _insert(array $info, $replace=false, $return_model=true, $load_model_from_source=true) {

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

            $this->cache_batch_start();

            // In case a blank record was cached
            cache_lib::set(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                static::ENTITY_NAME . ':' . self::RECORD . ':' . (static::BINARY_PK ? md5($info[static::PRIMARY_KEY]) : $info[static::PRIMARY_KEY]),
                $info
            );

            if (static::USING_COUNT) {
                if ($this->cache_engine_pool_read !== $this->cache_engine_pool_write) {
                    cache_lib::expire(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        static::ENTITY_NAME . ':' . self::COUNT,
                        $this->cache_delete_expire_ttl
                    );
                } else {
                    cache_lib::delete(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        static::ENTITY_NAME . ':' . self::COUNT
                    );
                }
            }

            $this->cache_batch_execute();

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
        protected function _inserts(array $infos, $keys_match=true, $replace=false, $return_collection=true, $load_models_from_source=true) {

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

            $this->cache_batch_start();

            cache_lib::set_multi(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                $insert_cache_data
            );

            if (static::USING_COUNT) {
                if ($this->cache_engine_pool_read !== $this->cache_engine_pool_write) {
                    cache_lib::expire(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        static::ENTITY_NAME . ':' . self::COUNT,
                        $this->cache_delete_expire_ttl
                    );
                } else {
                    cache_lib::delete(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        static::ENTITY_NAME . ':' . self::COUNT
                    );
                }
            }

            $this->cache_batch_execute();

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
        protected function _update(entity_record_model $model, array $new_info, $return_model=true, $reload_model_from_source=true) {

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

            $source_driver = "entity_record_limit_driver_{$this->source_engine}";
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

            $this->cache_batch_start();

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

            $this->cache_batch_execute();

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

            $source_driver = "entity_record_limit_driver_{$this->source_engine}";
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

            if (static::USING_COUNT) {
                if ($this->cache_engine_pool_read !== $this->cache_engine_pool_write) {
                    cache_lib::expire(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        static::ENTITY_NAME . ':' . self::COUNT,
                        $this->cache_delete_expire_ttl
                    );
                } else {
                    cache_lib::delete(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        static::ENTITY_NAME . ':' . self::COUNT
                    );
                }
            }

            $this->cache_batch_execute();

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

            $this->cache_batch_start();

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

            if (static::USING_COUNT) {
                if ($this->cache_engine_pool_read !== $this->cache_engine_pool_write) {
                    cache_lib::expire(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        static::ENTITY_NAME . ':' . self::COUNT,
                        $this->cache_delete_expire_ttl
                    );
                } else {
                    cache_lib::delete(
                        $this->cache_engine,
                        $this->cache_engine_pool_write,
                        static::ENTITY_NAME . ':' . self::COUNT
                    );
                }
            }

            $this->cache_batch_execute();

            return true;
        }
    }