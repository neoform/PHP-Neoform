<?php

    namespace neoform\entity;

    use neoform;

    abstract class dao {

        protected $source_engine;
        protected $source_engine_pool_read;
        protected $source_engine_pool_write;

        protected $cache_engine;
        protected $cache_engine_pool_read;
        protected $cache_engine_pool_write;

        protected $cache_meta_engine;
        protected $cache_meta_engine_pool_read;
        protected $cache_meta_engine_pool_write;

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

        // Meta cache key - this is a parent to all meta data lists
        const META = 'meta';

        // Special key - it's a subset of 'meta' - containing cache keys that must always be destroyed
        const ALWAYS = 'meta:always';

        public function __construct(array $config) {
            $this->source_engine                = $config['source_engine'];
            $this->source_engine_pool_read      = $config['source_engine_pool_read'];
            $this->source_engine_pool_write     = $config['source_engine_pool_write'];

            $this->cache_engine                 = $config['cache_engine'];
            $this->cache_engine_pool_read       = $config['cache_engine_pool_read'];
            $this->cache_engine_pool_write      = $config['cache_engine_pool_write'];

            $this->cache_meta_engine            = $config['cache_meta_engine'];
            $this->cache_meta_engine_pool_read  = $config['cache_meta_engine_pool_read'];
            $this->cache_meta_engine_pool_write = $config['cache_meta_engine_pool_write'];

            $this->cache_delete_expire_ttl      = $config['cache_delete_expire_ttl'];
        }

        /**
         * Get the field binding of a given column
         *
         * @param string $field_name name of column in this entity
         *
         * @return integer
         */
        public function field_binding($field_name) {
            return $this->field_bindings[$field_name];
        }

        /**
         * Get the field bindings of all columns
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
         * Build a cache key used by the cache_lib by combining the dao class name, the cache key and the variables
         * found in the $fieldvals
         *
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $fieldvals      optional - array of table keys and their values being looked up in the table
         *
         * @return string a cache key that is unqiue to the application
         */
        final protected static function _build_key($cache_key_name, array $fieldvals=[]) {
            // each key is namespaced with the name of the class, then the name of the function ($cache_key_name)
            $param_count = count($fieldvals);
            if ($param_count === 1) {
                return static::CACHE_KEY . ":{$cache_key_name}:" . md5(reset($fieldvals));
            } else if ($param_count === 0) {
                return static::CACHE_KEY . ":{$cache_key_name}:";
            } else {
                ksort($fieldvals);
                foreach ($fieldvals as & $val) {
                    $val = base64_encode($val);
                }
                // Use only the array_values() and not the named array, since each $cache_key_name is unique per function
                return static::CACHE_KEY . ":{$cache_key_name}:" . md5(json_encode(array_values($fieldvals)));
            }
        }

        /**
         * Build a list cache key with an optional field value
         *
         * @param string $field_name name of field/column
         * @param mixed  $fieldval   value of field/column
         *
         * @return string
         */
        final protected static function _build_key_list($field_name, $fieldval) {
            if ($fieldval === null) {
                return static::CACHE_KEY . ':' . self::META . "[{$field_name}]";
            } else {
                return static::CACHE_KEY . ':' . self::META . "[{$field_name}]:" . md5($fieldval);
            }
        }

        /**
         * Build a list cache key for entire fields (no values)
         *
         * @param String $field_name name of field/column
         *
         * @return string
         */
        final protected static function _build_key_list_field($field_name) {
            return static::CACHE_KEY . ':' . self::META . ":{$field_name}";
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
            return neoform\cache\lib::single(
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
            neoform\cache\lib::delete(
                $this->cache_engine,
                $this->cache_engine_pool_write,
                $key
            );
        }

        /**
         * Create the meta data (lists) to identify which cache keys to destroy when the record or field values have been changed
         *
         * @param string     $cache_key cache key for which we are storing meta data
         * @param array|null $fieldvals fields and values
         * @param array|null $fields    fields
         */
        final public function _set_meta_cache($cache_key, array $fieldvals=null, array $fields=null) {

            $list_keys = [];

            if ($fields) {
                foreach ($fields as $field) {
                    $list_keys[] = self::_build_key_list_field($field);
                }
            }

            if ($fieldvals) {
                foreach ($fields ? array_diff_key($fieldvals, array_flip($fields)) : $fieldvals as $field => $value) {
                    if (is_array($value)) {
                        foreach ($value as $val) {
                            $list_keys[] = self::_build_key_list($field, $val);
                        }
                    } else {
                        $list_keys[] = self::_build_key_list($field, $value);
                    }
                }
            } else {
                $list_keys[] = static::CACHE_KEY . ':' . self::ALWAYS;
            }

            // Create meta data lists
            neoform\entity\meta\lib::push(
                $this->cache_meta_engine,
                $this->cache_meta_engine_pool_write,
                $cache_key,
                $list_keys
            );
        }

        /**
         * Create the meta data (lists) to identify which cache keys to destroy when the record or field values have been changed
         *
         * @param array      $cache_keys cache key for which we are storing meta data
         * @param array|null $fields     fields
         */
        final public function _set_meta_cache_multi(array $cache_keys, array $fields=null) {

            $list_keys = [];

            if ($fields) {
                $build_key_list_fields = [];
                foreach ($fields as $field) {
                    $build_key_list_fields[] = self::_build_key_list_field($field);
                }

                foreach (array_keys($cache_keys) as $cache_key) {
                    $list_keys[$cache_key] = $build_key_list_fields;
                }
            }

            foreach ($cache_keys as $cache_key => $fieldvals) {
                if (is_array($fieldvals) && $fieldvals) {
                    foreach ($fields ? array_diff_key($fieldvals, array_flip($fields)) : $fieldvals as $field => $value) {
                        if (is_array($value)) {
                            foreach ($value as $val) {
                                $list_keys[$cache_key][] = self::_build_key_list($field, $val);
                            }
                        } else {
                            $list_keys[$cache_key][] = self::_build_key_list($field, $value);
                        }
                    }
                } else {
                    $list_keys[$cache_key][] = static::CACHE_KEY . ':' . self::ALWAYS;
                }
            }

            // Create meta data lists
            neoform\entity\meta\lib::push_multi(
                $this->cache_meta_engine,
                $this->cache_meta_engine_pool_write,
                $list_keys
            );
        }

        /**
         * Delete all cache keys and field/value and field order lists - by fields
         *
         * Do not wrap a batch execution around this function
         *
         * @param array $fieldvals list of fields/values
         */
        final protected function _delete_meta_cache(array $fieldvals) {

            // Always delete the stuff in the always list
            $list_keys = [ static::CACHE_KEY . ':' . self::ALWAYS ];

            foreach ($fieldvals as $field => $value) {

                $list_keys[] = self::_build_key_list_field($field);

                if (is_array($value)) {
                    foreach ($value as $val) {
                        $list_keys[] = self::_build_key_list($field, $val);
                    }
                } else {
                    $list_keys[] = self::_build_key_list($field, $value);
                }
            }

            if ($list_keys = array_unique($list_keys)) {
                $cache_keys = neoform\entity\meta\lib::pull(
                    $this->cache_meta_engine,
                    $this->cache_meta_engine_pool_write,
                    $list_keys
                );

                if ($cache_keys) {
                    if ($this->cache_engine_pool_read !== $this->cache_engine_pool_write) {
                        neoform\cache\lib::expire_multi(
                            $this->cache_engine,
                            $this->cache_engine_pool_write,
                            $cache_keys,
                            $this->cache_delete_expire_ttl
                        );
                    } else {
                        neoform\cache\lib::delete_multi(
                            $this->cache_engine,
                            $this->cache_engine_pool_write,
                            $cache_keys
                        );
                    }
                }
            }
        }
        /**
         * Delete all cache keys and field/value and field order lists - by fields
         *
         * Do not wrap a batch execution around this function
         *
         * @param array $fieldvals_arr array containing lists of fields/values
         */
        final protected function _delete_meta_cache_multi(array $fieldvals_arr) {
            $list_keys = [ static::CACHE_KEY . ':' . self::ALWAYS ];

            foreach ($fieldvals_arr as $fieldvals) {

                foreach ($fieldvals as $field => $value) {

                    $list_keys[] = self::_build_key_list_field($field);

                    if (is_array($value)) {
                        foreach ($value as $val) {
                            $list_keys[] = self::_build_key_list($field, $val);
                        }
                    } else {
                        $list_keys[] = self::_build_key_list($field, $value);
                    }
                }
            }

            if ($list_keys = array_unique($list_keys)) {
                $cache_keys = neoform\entity\meta\lib::pull(
                    $this->cache_meta_engine,
                    $this->cache_meta_engine_pool_write,
                    $list_keys
                );

                if ($cache_keys) {
                    if ($this->cache_engine_pool_read !== $this->cache_engine_pool_write) {
                        neoform\cache\lib::expire_multi(
                            $this->cache_engine,
                            $this->cache_engine_pool_write,
                            $cache_keys,
                            $this->cache_delete_expire_ttl
                        );
                    } else {
                        neoform\cache\lib::delete_multi(
                            $this->cache_engine,
                            $this->cache_engine_pool_write,
                            $cache_keys
                        );
                    }
                }
            }
        }
    }
