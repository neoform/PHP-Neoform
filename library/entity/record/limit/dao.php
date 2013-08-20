<?php

    /**
     * DAO limit class allows an entity to be looked up the same way as a regular DAO record, however it has the additional
     * ability to limit and order the lists or PKs that get looked up. Because the cache must always be accurate/fresh
     * lists of meta-data must be stored for each result set in order to identify which result sets (in cache) to destroy
     * when a record's field is updated.
     *
     * Eg, if a user's email address has changed, we must destroy any list of users that were 'order by email address ASC'
     * since that list is possibly no longer accurate. Also, since the result sets use limit/offsets, there are often
     * more than on result set cached away, so *all* cached result sets that used email to be ordered, most be destroyed.
     */
    abstract class entity_record_limit_dao extends entity_record_dao {

        const SORT_ASC  = 0;
        const SORT_DESC = 1;

        // List key - Always clear these keys on every change
        const ALWAYS  = 'always';

        // List key - Limit lists
        const LIMIT  = 'limit';

        // Offset key - Offset related record PK lists
        const OFFSET = 'offset';

        protected $cache_list_engine;
        protected $cache_list_engine_pool_read;
        protected $cache_list_engine_pool_write;

        public function __construct(array $config) {

            parent::__construct($config);

            $this->cache_list_engine            = $config['cache_list_engine'];
            $this->cache_list_engine_pool_read  = $config['cache_list_engine_pool_read'];
            $this->cache_list_engine_pool_write = $config['cache_list_engine_pool_write'];
        }

        /**
         * Start batched query pipeline
         */
        final protected function cache_list_batch_start() {
            cache_lib::pipeline_start(
                $this->cache_list_engine,
                $this->cache_list_engine_pool_write
            );
        }

        /**
         * Execute batched cache queries
         *
         * @return mixed result from batch execution
         */
        final protected function cache_list_batch_execute() {
            return cache_lib::pipeline_execute(
                $this->cache_list_engine,
                $this->cache_list_engine_pool_write
            );
        }

        /**
         * Build a list cache key with an optional field value
         *
         * @param String $field_name     name of field/column
         * @param mixed  $field_value    value of field/column
         * @param string $entity_name    optional - closure function that retreieves the recordset from its origin
         *
         * @return string
         */
        final public static function _build_key_list($field_name, $field_value=null, $entity_name=null) {
            if ($field_value === null) {
                return ($entity_name ?: static::ENTITY_NAME) . ':' . self::LIMIT . "[{$field_name}]";
            } else {
                return ($entity_name ?: static::ENTITY_NAME) . ':' . self::LIMIT . "[{$field_name}]:" . md5($field_value);
            }
        }

        /**
         * Build a list cache key for ordered fields
         *
         * @param String $field_name     name of field/column
         * @param string $entity_name    optional - closure function that retreieves the recordset from its origin
         *
         * @return string
         */
        final public static function _build_key_order($field_name, $entity_name=null) {
            return ($entity_name ?: static::ENTITY_NAME) . ':' . self::LIMIT . ":order_by[{$field_name}]";
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
         * @param string       $entity_name    optional - closure function that retreieves the recordset from its origin
         * @return string a cache key that is unqiue to the application
         */
        final public static function _build_key_offset($cache_key_name, array $order_by, $offset, $limit=null, array $params=[], $entity_name=null) {
            ksort($order_by);

            // each key is namespaced with the name of the class, then the name of the function ($cache_key_name)
            $param_count = count($params);
            if ($param_count === 1) {
                return ($entity_name ?: static::ENTITY_NAME) . ":{$cache_key_name}:{$offset},{$limit}:" . md5(json_encode($order_by)) . ':' . md5(reset($params));
            } else if ($param_count === 0) {
                return ($entity_name ?: static::ENTITY_NAME) . ":{$cache_key_name}:{$offset},{$limit}:" . md5(json_encode($order_by)) . ':';
            } else {
                ksort($params);
                foreach ($params as & $param) {
                    $param = base64_encode($param);
                }
                // Use only the array_values() and not the named array, since each $cache_key_name is unique per function
                return ($entity_name ?: static::ENTITY_NAME) . ":{$cache_key_name}:{$offset},{$limit}:" . md5(json_encode($order_by)) . ':' . md5(json_encode(array_values($params)));
            }
        }

        /**
         * Get fields by an integer offset
         *
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $keys           array of fields/values being looked up in the table
         * @param array   $order_by       array of fields to order by - key = field, val = order direction
         * @param integer $offset         records starting at what offset
         * @param integer $limit          max number of record to return
         *
         * @return mixed
         */
        final protected function _by_fields_offset($cache_key_name, array $keys, array $order_by, $offset, $limit) {

            $limit  = (int) $limit;
            $offset = $offset === null ? null : (int) $offset;

            $pk   = static::PRIMARY_KEY;
            $self = $this;

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
                    $source_driver = "entity_record_limit_driver_{$self->source_engine}";
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
                    $self->_set_delete_limit_cache_lists($self, $cache_key, $keys, $order_by);
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
         * @param array   $order_by       array of fields to order by - key = field, val = order direction
         * @param integer $offset         records starting at what offset
         * @param integer $limit          max number of record to return
         * @return array  pks of records from cache
         * @throws model_exception
         */
        final protected function _by_fields_offset_multi($cache_key_name, array $keys_arr, array $order_by, $offset, $limit) {

            $limit  = (int) $limit;
            $offset = $offset === null ? null : (int) $offset;

            $pk   = static::PRIMARY_KEY;
            $self = $this;

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
                        $fields,
                        $self::ENTITY_NAME
                    );
                },
                function(array $keys_arr) use ($self, $pk, $order_by, $offset, $limit) {
                    $source_driver = "entity_record_limit_driver_{$self->source_engine}";
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
                    $self->_set_delete_limit_cache_lists_multi($self, $cache_keys, $order_by);
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
                parent::_build_key(self::COUNT, $keys ?: []),
                function() use ($self, $keys) {
                    $source_driver = "entity_record_limit_driver_{$self->source_engine}";
                    return $source_driver::count($self, $self->source_engine_pool_read, $keys);
                },
                function($cache_key) use ($self, $keys) {
                    $self->_set_delete_count_cache_lists($self, $cache_key, $keys);
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
        protected function _insert(array $info, $replace=false, $return_model=true, $load_model_from_source=false) {

            $source_driver = "entity_record_limit_driver_{$this->source_engine}";
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

            $source_driver = "entity_record_limit_driver_{$this->source_engine}";
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

            // Destroy cache based on table fields - do not wrap this function in a batch execution
            self::_delete_limit_cache_by_fields(array_keys(static::pdo_bindings()));

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

            $source_driver = "entity_record_limit_driver_{$this->source_engine}";
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

            $this->cache_batch_execute();

            // Destroy cache based on table fields - do not wrap this function in a batch execution
            self::_delete_limit_cache_by_fields(array_keys(static::pdo_bindings()));

            return true;
        }

        /**
         * Create the meta data (lists) to identify which cache keys to destroy when the record or field values have been changed
         *
         * @param entity_record_limit_dao $self
         * @param string                  $cache_key
         * @param array                   $keys
         * @param array                   $order_by
         */
        final protected function _set_delete_limit_cache_lists(entity_record_limit_dao $self, $cache_key, array $keys, array $order_by) {

            $this->cache_batch_start();

            /**
             * Build lists of keys for deletion - when it's time to delete/modify the record
             */

            $entity_name = $self::ENTITY_NAME;

            /**
             * Order by - goes first, since it's wider reaching, if there is overlap between $order_by fields
             * and $keys fields, we wont use those fields in $keys. (since they'll both contain the same cache
             * keys to destroy.
             *
             * An entry for each $order_by field must be created (linking back to this set's $cache_key)
             */
            foreach ($order_by as $field => $direction) {
                // Create list key for order by field
                $order_by_list_key = self::_build_key_order($field, $entity_name);

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
                    self::_build_key_list($field, null, $entity_name),
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
                $list_key = self::_build_key_list($field, $value, $entity_name);

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
                    self::_build_key_list($field, null, $entity_name),
                    $list_key
                );
            }

            $this->cache_batch_execute();
        }

        /**
         * Create the meta data (lists) to identify which cache keys to destroy when the record or field values have been changed
         *
         * @param entity_record_limit_dao $self
         * @param array                   $cache_keys
         * @param array                   $order_by
         */
        final protected function _set_delete_limit_cache_lists_multi(entity_record_limit_dao $self, array $cache_keys, array $order_by) {

            $this->cache_batch_start();

            /**
             * Build lists of keys for deletion - when it's time to delete/modify the record
             */

            $entity_name = $self::ENTITY_NAME;

            /**
             * Order by - goes first, since it's wider reaching, if there is overlap between $order_by fields
             * and $keys fields, we wont use those fields in $keys. (since they'll both contain the same cache
             * keys to destroy.
             *
             * An entry for each $order_by field must be created (linking back to this set's $cache_key)
             */
            foreach ($order_by as $field => $direction) {
                // Create list key for order by field
                $order_by_list_key = self::_build_key_order($field, $entity_name);

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
                    self::_build_key_list($field, null, $entity_name),
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
                    $list_key = self::_build_key_list($field, $value, $entity_name);

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
                        self::_build_key_list($field, null, $entity_name),
                        $list_key
                    );
                }
            }

            $this->cache_batch_execute();
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
            $list_keys            = [ parent::_build_key(self::ALWAYS), ];
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
                $list_keys = array_unique(
                    array_merge(
                        $list_keys,
                        cache_lib::list_get_union(
                            $this->cache_list_engine,
                            $this->cache_list_engine_pool_write,
                            $field_list_keys
                        )
                    )
                );
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

            $this->cache_batch_start();

            if ($this->cache_list_engine !== $this->cache_engine || $this->cache_list_engine_pool_write !== $this->cache_engine_pool_write) {
                $this->cache_list_batch_start();
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
                $this->cache_list_batch_execute();
            }

            $this->cache_batch_execute();
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
            $list_keys            = [ parent::_build_key(self::ALWAYS), ];
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
                $list_keys = array_unique(
                    array_merge(
                        $list_keys,
                        cache_lib::list_get_union(
                            $this->cache_list_engine,
                            $this->cache_list_engine_pool_write,
                            $field_list_keys
                        )
                    )
                );
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

            $this->cache_batch_start();

            if ($this->cache_list_engine !== $this->cache_engine || $this->cache_list_engine_pool_write !== $this->cache_engine_pool_write) {
                $this->cache_list_batch_start();
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
                $this->cache_list_batch_execute();
            }

            $this->cache_batch_execute();
        }

        /**
         * Create the meta data (lists) to identify which cache keys to destroy when the record or field values have been changed
         *
         * @param string     $self
         * @param string     $cache_key
         * @param array|null $keys
         */
        final public function _set_delete_count_cache_lists($self, $cache_key, array $keys=null) {

            /**
             * Build lists of keys for deletion - when it's time to delete/modify the record
             */

            $entity_name = $self::ENTITY_NAME;

            if ($keys) {
                $this->cache_batch_start();

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
                    $list_key = self::_build_key_list($field, $value, $entity_name);

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
                        self::_build_key_list($field, null, $entity_name),
                        $list_key
                    );
                }

                $this->cache_batch_execute();
            } else {
                // Add the $list_key key to field list key - if it doesn't already exist
                cache_lib::list_add(
                    $this->cache_list_engine,
                    $this->cache_list_engine_pool_write,
                    parent::_build_key(self::ALWAYS),
                    $cache_key
                );
            }
        }
    }
