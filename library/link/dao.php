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

        /**
         * Get the record DAO driver name (name derived from db connection type)
         *
         * @return string
         */
        protected static function driver() {
            static $driver;
            if (! $driver) {
                $driver = 'link_driver_' . core::sql('slave')->driver();
            }
            return $driver;
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
        final protected static function _single($key, callable $get) {
            return cache_lib::single(
                static::CACHE_ENGINE,
                $key,
                static::ENTITY_POOL,
                $get
            );
        }

        /**
         * Start batched query pipeline
         */
        final protected static function cache_batch_start() {
            cache_lib::pipeline_start(
                static::CACHE_ENGINE,
                static::ENTITY_POOL
            );
        }

        /**
         * Execute batched cache queries
         *
         * @return mixed result from batch execution
         */
        final protected static function cache_batch_execute() {
            return cache_lib::pipeline_execute(
                static::CACHE_ENGINE,
                static::ENTITY_POOL
            );
        }

        /**
         * Delete a cached record
         *
         * @access protected
         * @static
         * @final
         * @param string   $key full cache key with namespace - it's recomended that record_dao::_build_key() is used to create this key
         * @return boolean result of the cache being deleted
         */
        final protected static function _cache_delete($key) {
            return cache_lib::delete(
                static::CACHE_ENGINE,
                $key,
                static::ENTITY_POOL
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
        final public static function _build_key($cache_key_name, array $params=[], $entity_name=null) {
            // each key is namespaced with the name of the class
            if (count($params) === 1) {
                //base64_encode incase the value is binary or something
                return ($entity_name ?: static::ENTITY_NAME) . ":$cache_key_name:" . md5(base64_encode(current($params)));
            } else {
                ksort($params);
                return ($entity_name ?: static::ENTITY_NAME) . ":$cache_key_name:" . md5(json_encode(array_values($params)));
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
        final protected static function _by_fields($cache_key_name, array $select_fields, array $keys) {

            $self = static::ENTITY_NAME . '_dao';

            return cache_lib::single(
                static::CACHE_ENGINE,
                self::_build_key($cache_key_name, $keys),
                static::ENTITY_POOL,
                function() use ($self, $select_fields, $keys) {
                    $driver = $self::driver();
                    return $driver::by_fields($self, $select_fields, $keys);
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
        final protected static function _by_fields_multi($cache_key_name, array $select_fields, array $keys_arr) {

            $self = static::ENTITY_NAME . '_dao';

            return cache_lib::multi(
                static::CACHE_ENGINE,
                $keys_arr,
                function($fields) use ($self, $cache_key_name) {
                    return record_dao::_build_key($cache_key_name, $fields, $self);
                },
                static::ENTITY_POOL,
                function($keys_arr) use ($self, $select_fields) {
                    $driver = $self::driver();
                    return $driver::by_fields_multi($self, $select_fields, $keys_arr);
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
        protected static function _insert(array $info, $replace=false) {
            $self   = static::ENTITY_NAME . '_dao';
            $driver = $self::driver();
            return $driver::insert($self, $info, $replace);
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
        protected static function _inserts(array $infos, $replace=false) {
            if (! count($infos)) {
                return;
            }

            $self   = static::ENTITY_NAME . '_dao';
            $driver = $self::driver();
            return $driver::inserts($self, $infos, $replace);
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
        protected static function _update(array $new_info, array $where) {
            if (count($new_info)) {
                $self   = static::ENTITY_NAME . '_dao';
                $driver = $self::driver();
                return $driver::update($self, $new_info, $where);
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
        protected static function _delete(array $keys) {
            $self   = static::ENTITY_NAME . '_dao';
            $driver = $self::driver();
            return $driver::delete($self, $keys);
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
        protected static function _deletes(array $keys_arr) {
            $self   = static::ENTITY_NAME . '_dao';
            $driver = $self::driver();
            return $driver::deletes($self, $keys_arr);
        }
    }





