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
    abstract class entity_link_dao extends entity_dao {

        /**
         * Build a cache key used by the cache_lib by combining the dao class name, the cache key and the variables found in the $params
         *
         * @access public
         * @static
         * @final
         * @param string       $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param string       $select_field
         * @param array        $order_by       optional - array of order bys
         * @param integer      $offset         what starting position to get records from
         * @param integer|null $limit          how many records to select
         * @param array        $params         optional - array of table keys and their values being looked up in the table
         * @return string a cache key that is unqiue to the application
         */
        final protected static function _build_key_limit($cache_key_name, $select_field, array $order_by, $offset=null,
                                                         $limit=null, array $params=[]) {
            ksort($order_by);

            // each key is namespaced with the name of the class, then the name of the function ($cache_key_name)
            $param_count = count($params);
            if ($param_count === 1) {
                return static::ENTITY_NAME . ":{$cache_key_name}:{$select_field}:{$offset},{$limit}:" .
                       md5(json_encode($order_by)) . ':' . md5(reset($params));
            } else if ($param_count === 0) {
                return static::ENTITY_NAME . ":{$cache_key_name}:{$select_field}:{$offset},{$limit}:" .
                       md5(json_encode($order_by)) . ':';
            } else {
                ksort($params);
                foreach ($params as & $param) {
                    $param = base64_encode($param);
                }
                // Use only the array_values() and not the named array, since each $cache_key_name is unique per function
                return static::ENTITY_NAME . ":{$cache_key_name}:{$select_field}:{$offset},{$limit}:" .
                       md5(json_encode($order_by)) . ':' . md5(json_encode(array_values($params)));
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
         * @param array   $fieldvals      array of table keys and their values being looked up in the table
         * @param array   $order_by
         * @param null    $limit
         * @param null    $offset
         * @return array  array of records from cache
         * @throws entity_exception
         */
        final protected function _by_fields($cache_key_name, array $select_fields, array $fieldvals, array $order_by=null,
                                            $limit=null, $offset=null) {

            if ($order_by) {
                $select_field = reset($select_fields); // if more than one key is provided, too bad, only the first is getting used

                $limit  = (int) $limit;
                $offset = $offset === null ? null : (int) $offset;

                if (! isset($this->foreign_keys[$select_field])) {
                    throw new entity_exception("Unknown foreign key field \"{$select_field}\" in " . $this::ENTITY_NAME . '.');
                }

                $foreign_dao = entity::dao($this->foreign_keys[$select_field]);

                $cache_key = self::_build_key_limit(
                    $cache_key_name,
                    $select_field,
                    $order_by,
                    (int) $offset,
                    (int) $limit,
                    $fieldvals
                );

                return cache_lib::single(
                    $this->cache_engine,
                    $this->cache_engine_pool_read,
                    $this->cache_engine_pool_write,
                    $cache_key,
                    function() use ($select_field, $fieldvals, $foreign_dao, $order_by, $limit, $offset) {
                        $source_driver = "entity_link_driver_{$this->source_engine}";
                        return $source_driver::by_fields_limit(
                            $this,
                            $this->source_engine_pool_read,
                            $select_field,
                            $foreign_dao,
                            $fieldvals,
                            $order_by,
                            $limit,
                            $offset
                        );
                    },
                    function($cache_key) use ($select_field, $fieldvals, $order_by, $foreign_dao) {

                        // Local DAO
                        $this->_set_meta_cache($cache_key, $fieldvals, [ $select_field ]);

                        // Foreign DAO
                        $order_by[$foreign_dao::PRIMARY_KEY] = true; // add primary key to the list of fields
                        $foreign_dao->_set_meta_cache($cache_key, [], array_keys($order_by));
                    }
                );
            } else {
                return cache_lib::single(
                    $this->cache_engine,
                    $this->cache_engine_pool_read,
                    $this->cache_engine_pool_write,
                    parent::_build_key($cache_key_name, $fieldvals),
                    function() use ($select_fields, $fieldvals, $order_by, $limit, $offset) {
                        $source_driver = "entity_link_driver_{$this->source_engine}";
                        return $source_driver::by_fields(
                            $this,
                            $this->source_engine_pool_read,
                            $select_fields,
                            $fieldvals,
                            $order_by,
                            $limit,
                            $offset
                        );
                    },
                    function($cache_key) use ($select_fields, $fieldvals, $order_by) {

                        foreach ($select_fields as $select_field) {
                            $order_by[$select_field] = true;
                        }

                        $this->_set_meta_cache($cache_key, $fieldvals, array_keys($order_by));
                    }
                );
            }
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
         * @throws entity_exception
         */
        final protected function _by_fields_multi($cache_key_name, array $select_fields, array $keys_arr) {
            return cache_lib::multi(
                $this->cache_engine,
                $this->cache_engine_pool_read,
                $this->cache_engine_pool_write,
                $keys_arr,
                function($fields) use ($cache_key_name) {
                    return $this::_build_key($cache_key_name, $fields, $this::ENTITY_NAME);
                },
                function($keys_arr) use ($select_fields) {
                    $source_driver = "entity_link_driver_{$this->source_engine}";
                    return $source_driver::by_fields_multi($this, $this->source_engine_pool_read, $select_fields, $keys_arr);
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
         * @throws entity_exception
         */
        protected function _insert(array $info, $replace=false) {
            $source_driver = "entity_link_driver_{$this->source_engine}";
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
         * @throws entity_exception
         */
        protected function _inserts(array $infos, $replace=false) {
            if (! $infos) {
                return;
            }

            $source_driver = "entity_link_driver_{$this->source_engine}";
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
         * @throws entity_exception
         */
        protected function _update(array $new_info, array $where) {
            if ($new_info) {
                $source_driver = "entity_link_driver_{$this->source_engine}";
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
         * @throws entity_exception
         */
        protected function _delete(array $keys) {
            $source_driver = "entity_link_driver_{$this->source_engine}";
            return $source_driver::delete($this, $this->source_engine_pool_write, $keys);
        }

        /**
         * Delete linking records from the database
         *
         * @access protected
         * @static
         * @param array $keys_arr arrays matching the PKs of the link
         * @return boolean returns true on success
         * @throws entity_exception
         */
        protected function _deletes(array $keys_arr) {
            $source_driver = "entity_link_driver_{$this->source_engine}";
            return $source_driver::deletes($this, $this->source_engine_pool_write, $keys_arr);
        }
    }
