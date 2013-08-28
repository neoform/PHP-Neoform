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
         * Build a cache key used by the cache_lib by combining the dao class name, the cache key and the variables found in the $params
         *
         * @access public
         * @static
         * @final
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $params         optional - array of table keys and their values being looked up in the table
         * @return string a cache key that is unqiue to the application
         */
        final protected static function _build_key($cache_key_name, array $params=[]) {
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
        final protected static function _build_key_list($field_name, $field_value=null) {
            if ($field_value === null) {
                return static::ENTITY_NAME . ':' . self::META . "[{$field_name}]";
            } else {
                return static::ENTITY_NAME . ':' . self::META . "[{$field_name}]:" . md5($field_value);
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
    }