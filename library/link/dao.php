<?php

    /**
     * Link DAO Standard database access, for accessing tables that do not have a single primary key but instead a
     * composite key (2 column PK) that link two other tables together.
     *
     * It is strongly discouraged to include any other fields in this record type, as it breaks the convention of a
     * linking table. If you must have a linking record with additional fields, use a record entity instead.
     *
     * REQUIRED: every entity class must have the following constants in it (via definition file):
     *    string TABLE       the table name in the database
     *    string ENTITY_NAME the base name of the entity (usually the same as TABLE unless different for a specific reason)
     *    string ENTITY_POOL must have a corresponding entry in the config file for the caching engine being used, eg (core::config()->memcache['pools'] = 'entities')
     */
    abstract class link_dao {

        protected $source_engine;
        protected $source_engine_pool_read;
        protected $source_engine_pool_write;

        protected $cache_engine;
        protected $cache_engine_pool_read;
        protected $cache_engine_pool_write;

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
         * Get a cached link
         *
         * @access protected
         * @static
         * @final
         * @param string   $key full cache key with namespace - it's recomended that record_dao::_build_key() is used to create this key
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
         * @param string   $key full cache key with namespace - it's recomended that record_dao::_build_key() is used to create this key
         */
        final protected function _cache_delete($key) {
            cache_lib::delete(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                $key
            );
        }

        /**
         * Build a cache key used by the cache_lib by combining the dao class name, the cache key and the variables found in the $params
         *
         * @access public
         * @static
         * @final
         * @param string $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array  $params         optional - array of table keys and their values being looked up in the table
         * @param string $entity_name    optional - name of the entity doing the query
         * @return string a cache key that is unqiue to the application
         */
        final public function _build_key($cache_key_name, array $params=[], $entity_name=null) {
            // each key is namespaced with the name of the class
            if (count($params) === 1) {
                //base64_encode incase the value is binary or something
                return ($entity_name ?: static::ENTITY_NAME) . ":{$cache_key_name}:" . md5(current($params));
            } else {
                ksort($params);
                return ($entity_name ?: static::ENTITY_NAME) . ":{$cache_key_name}:" . md5(json_encode(array_values($params)));
            }
        }


        /**
         * Gets fields that match the $keys, this gets the columns specified by $select_fields
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
        final protected function _by_fields($cache_key_name, array $select_fields, array $keys) {

            $self = $this;

            return cache_lib::single(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                self::_build_key($cache_key_name, $keys),
                function() use ($self, $select_fields, $keys) {
                    $source_driver = "link_driver_{$self->source_engine}";
                    return $source_driver::by_fields($self, $self->source_engine_pool_read, $select_fields, $keys);
                }
            );
        }

        /**
         * Gets the ids of more than one set of key values
         *
         * @access protected
         * @static
         * @final
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $select_fields  array of table fields (table columns) to be selected
         * @param array   $keys_arr       array of arrays of table keys and their values being looked up in the table - each sub-array must have matching keys
         * @return array  ids of records from cache
         * @throws model_exception
         */
        final protected function _by_fields_multi($cache_key_name, array $select_fields, array $keys_arr) {

            $self = $this;

            return cache_lib::multi(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                $keys_arr,
                function($fields) use ($self, $cache_key_name) {
                    return record_dao::_build_key($cache_key_name, $fields, $self::ENTITY_NAME);
                },
                function($keys_arr) use ($self, $select_fields) {
                    $source_driver = "link_driver_{$self->source_engine}";
                    return $source_driver::by_fields_multi($self, $self->source_engine_pool_read, $select_fields, $keys_arr);
                }
            );
        }

        /**
         * Inserts a linking record into the database
         *
         * @access protected
         * @static
         * @param array   $info    an associative array of into to be put info the database
         * @param boolean $replace optional - user REPLACE INTO instead of INSERT INTO
         * @return boolean result of the PDO::execute()
         * @throws model_exception
         */
        protected function _insert(array $info, $replace=false) {
            $source_driver = "link_driver_{$this->source_engine}";
            return $source_driver::insert($this, $this->source_engine_pool_write, $info, $replace);
        }

        /**
         * Inserts more than one linking record into the database at a time
         *
         * @access protected
         * @static
         * @param array   $infos   an array of associative array of info to be put into the database
         * @param boolean $replace optional - user REPLACE INTO instead of INSERT INTO
         * @return boolean result of the PDO::execute()
         * @throws model_exception
         */
        protected function _inserts(array $infos, $replace=false) {
            if (! $infos) {
                return;
            }

            $source_driver = "link_driver_{$this->source_engine}";
            return $source_driver::inserts($this, $this->source_engine_pool_write, $infos, $replace);
        }

        /**
         * Updates linking records in the database
         *
         * @access protected
         * @static
         * @param array $new_info the new info to be put into the model
         * @param array $where    return a model of the new record
         * @return boolean|null result of the PDO::execute()
         * @throws model_exception
         */
        protected function _update(array $new_info, array $where) {
            if ($new_info) {
                $source_driver = "link_driver_{$this->source_engine}";
                return $source_driver::update($this, $this->source_engine_pool_write, $new_info, $where);
            }
        }

        /**
         * Delete linking records from the database
         *
         * @access protected
         * @static
         * @param array $keys the where of the query
         * @return boolean result of the PDO::execute()
         * @throws model_exception
         */
        protected function _delete(array $keys) {
            $source_driver = "link_driver_{$this->source_engine}";
            return $source_driver::delete($this, $this->source_engine_pool_write, $keys);
        }

        /**
         * Delete linking records from the database
         *
         * @access protected
         * @static
         * @param array of arrays matching the PKs of the link
         * @return boolean returns true on success
         * @throws model_exception
         */
        protected function _deletes(array $keys_arr) {
            $source_driver = "link_driver_{$this->source_engine}";
            return $source_driver::deletes($this, $this->source_engine_pool_write, $keys_arr);
        }
    }
