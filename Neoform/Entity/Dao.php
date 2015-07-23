<?php

    namespace Neoform\Entity;

    use Neoform;

    /**
     * Class Dao
     *
     * @package Neoform\Entity
     */
    abstract class Dao {

        /**
         * @var array
         */
        protected $fieldBindings;

        /**
         * @var Repo\Cache
         */
        protected $cacheRepo;

        /**
         * @var Repo\Cache\Driver
         */
        protected $cacheRepoRead;

        /**
         * @var Repo\Cache\Driver
         */
        protected $cacheRepoWrite;

        /**
         * @var bool
         */
        protected $cacheReposAreTheSame;

        /**
         * @var Repo\MetaCache\Driver
         */
        protected $cacheMetaRepo;

        /**
         * @var int
         */
        protected $cacheDeleteExpireTtl;

        /**
         * @var bool
         */
        protected $useBinaryCacheKeys;

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

        /**
         * @param Config\Overridden $config
         */
        public function __construct(Config\Overridden $config) {

            /**
             * Cache Repos
             */
            $this->cacheReposAreTheSame = $config->getCacheEnginePoolRead() == $config->getCacheEnginePoolWrite();

            $this->cacheRepoRead = Repo\Cache\Lib::getRepo(
                $config->getCacheEngine(),
                $config->getCacheEnginePoolRead()
            );

            if ($this->cacheReposAreTheSame) {
                $this->cacheRepoWrite = $this->cacheRepoRead;
            } else {
                $this->cacheRepoWrite = Repo\Cache\Lib::getRepo(
                    $config->getCacheEngine(),
                    $config->getCacheEnginePoolWrite()
                );
            }

            /**
             * Cache Repo Lib (for more complex repo operations)
             */
            $this->cacheRepo = new Repo\Cache($this->cacheRepoRead, $this->cacheRepoWrite);

            /**
             * Meta cache engine
             */
            $this->cacheMetaRepo = Repo\MetaCache\Lib::getRepo(
                $config->getCacheMetaEngine(),
                $config->getCacheMetaEnginePool()
            );

            $this->cacheDeleteExpireTtl = $config->getCacheDeleteExpireTtl();
            $this->useBinaryCacheKeys   = $config->isCacheUsingBinaryKeys();
        }

        /**
         * Get the field binding of a given column
         *
         * @param string $fieldName name of column in this entity
         *
         * @return int
         */
        public function fieldBinding($fieldName) {
            return $this->fieldBindings[$fieldName];
        }

        /**
         * Get the field bindings of all columns
         *
         * @return array
         */
        public function fieldBindings() {
            return $this->fieldBindings;
        }

        /**
         * Bind an field's values to its appropriate variable type
         *
         * @param string $fieldName
         * @param mixed  $value
         *
         * @return mixed|null
         */
        protected function bindField($fieldName, $value) {
            if ($value === null) {
                return null;
            }

            switch ($this->fieldBindings[$fieldName]) {
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
        protected function bindFields(array &$fields) {
            foreach ($fields as $k => &$v) {
                if ($v === null) {
                    continue;
                }

                switch ($this->fieldBindings[$k]) {
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
         * found in the $fieldVals
         *
         * @param string  $cacheKey_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $fieldVals      optional - array of table keys and their values being looked up in the table
         *
         * @return string a cache key that is unqiue to the application
         */
        final protected function _buildKey($cacheKey_name, array $fieldVals=[]) {
            // each key is namespaced with the name of the class, then the name of the function ($cacheKey_name)
            $paramCount = count($fieldVals);
            if ($paramCount === 1) {
                return static::CACHE_KEY . ":{$cacheKey_name}:" . md5(reset($fieldVals) . ':' . key($fieldVals), $this->useBinaryCacheKeys);
            } else if ($paramCount === 0) {
                return static::CACHE_KEY . ":{$cacheKey_name}:";
            }

            ksort($fieldVals);
            foreach ($fieldVals as & $val) {
                $val = base64_encode($val);
            }

            return static::CACHE_KEY . ":{$cacheKey_name}:" . md5(
                json_encode($fieldVals),
                $this->useBinaryCacheKeys
            );
        }

        /**
         * Build a list cache key with an optional field value
         *
         * @param string $fieldName name of field/column
         * @param mixed  $fieldVal   value of field/column
         *
         * @return string
         */
        final protected function _buildKeyList($fieldName, $fieldVal) {
            if ($fieldVal === null) {
                return static::CACHE_KEY . ':' . self::META . "[{$fieldName}]";
            } else {
                return static::CACHE_KEY . ':' . self::META . "[{$fieldName}]:" . md5($fieldVal, $this->useBinaryCacheKeys);
            }
        }

        /**
         * Build a list cache key for entire fields (no values)
         *
         * @param String $fieldName name of field/column
         *
         * @return string
         */
        final protected function _buildKeyListField($fieldName) {
            return static::CACHE_KEY . ':' . self::META . ":{$fieldName}";
        }

        /**
         * Create the meta data (lists) to identify which cache keys to destroy when the record or field values have been changed
         *
         * @param string     $cacheKey cache key for which we are storing meta data
         * @param array|null $fieldVals fields and values
         * @param array|null $fields    fields
         */
        final public function _setMetaCache($cacheKey, array $fieldVals=null, array $fields=null) {

            $listKeys = [];

            if ($fields) {
                foreach ($fields as $field) {
                    $listKeys[] = $this->_buildKeyListField($field);
                }
            }

            if ($fieldVals) {
                foreach ($fields ? array_diff_key($fieldVals, array_flip($fields)) : $fieldVals as $field => $value) {
                    if (is_array($value)) {
                        foreach ($value as $val) {
                            $listKeys[] = $this->_buildKeyList($field, $val);
                        }
                    } else {
                        $listKeys[] = $this->_buildKeyList($field, $value);
                    }
                }
            } else {
                $listKeys[] = static::CACHE_KEY . ':' . self::ALWAYS;
            }

            // Create meta data lists
            $this->cacheMetaRepo->listAppend(
                $listKeys,
                $cacheKey
            );
        }

        /**
         * Create the meta data (lists) to identify which cache keys to destroy when the record or field values have been changed
         *
         * @param array      $cacheKeys cache key for which we are storing meta data
         * @param array|null $fields     fields
         */
        final public function _setMetaCacheMulti(array $cacheKeys, array $fields=null) {

            $listKeys = [];

            if ($fields) {
                $buildKeyListFields = [];
                foreach ($fields as $field) {
                    $buildKeyListFields[] = $this->_buildKeyListField($field);
                }

                foreach (array_keys($cacheKeys) as $cacheKey) {
                    $listKeys[$cacheKey] = $buildKeyListFields;
                }
            }

            foreach ($cacheKeys as $cacheKey => $fieldVals) {
                if (is_array($fieldVals) && $fieldVals) {
                    foreach ($fields ? array_diff_key($fieldVals, array_flip($fields)) : $fieldVals as $field => $value) {
                        if (is_array($value)) {
                            foreach ($value as $val) {
                                $listKeys[$cacheKey][] = $this->_buildKeyList($field, $val);
                            }
                        } else {
                            $listKeys[$cacheKey][] = $this->_buildKeyList($field, $value);
                        }
                    }
                } else {
                    $listKeys[$cacheKey][] = static::CACHE_KEY . ':' . self::ALWAYS;
                }
            }

            // Create meta data lists
            $this->cacheMetaRepo->listAppendMulti(
                $listKeys
            );
        }

        /**
         * Delete all cache keys and field/value and field order lists - by fields
         *
         * Do not wrap a batch execution around this function
         *
         * @param array $fieldVals list of fields/values
         */
        final protected function _deleteMetaCache(array $fieldVals) {

            // Always delete the stuff in the always list
            $listKeys = [ static::CACHE_KEY . ':' . self::ALWAYS, ];

            foreach ($fieldVals as $field => $value) {

                $listKeys[] = $this->_buildKeyListField($field);

                if (is_array($value)) {
                    foreach ($value as $val) {
                        $listKeys[] = $this->_buildKeyList($field, $val);
                    }
                } else {
                    $listKeys[] = $this->_buildKeyList($field, $value);
                }
            }

            $listKeys = array_unique($listKeys);

            if (! $listKeys) {
                return;
            }

            $cacheKeys = $this->cacheMetaRepo->listPull($listKeys);

            if (! $cacheKeys) {
                return;
            }

            if ($this->cacheReposAreTheSame) {
                $this->cacheRepoWrite->deleteMulti($cacheKeys);
            } else {
                $this->cacheRepoWrite->expireMulti(
                    $cacheKeys,
                    $this->cacheDeleteExpireTtl
                );
            }
        }

        /**
         * Delete all cache keys and field/value and field order lists - by fields
         *
         * Do not wrap a batch execution around this function
         *
         * @param array $fieldValsArr array containing lists of fields/values
         */
        final protected function _deleteMetaCacheMulti(array $fieldValsArr) {
            $listKeys = [ static::CACHE_KEY . ':' . self::ALWAYS, ];

            foreach ($fieldValsArr as $fieldVals) {

                foreach ($fieldVals as $field => $value) {

                    $listKeys[] = $this->_buildKeyListField($field);

                    if (is_array($value)) {
                        foreach ($value as $val) {
                            $listKeys[] = $this->_buildKeyList($field, $val);
                        }
                    } else {
                        $listKeys[] = $this->_buildKeyList($field, $value);
                    }
                }
            }

            $listKeys = array_unique($listKeys);

            if (! $listKeys) {
                return;
            }

            $cacheKeys = $this->cacheMetaRepo->listPull($listKeys);

            if (! $cacheKeys) {
                return;
            }

            if ($this->cacheReposAreTheSame) {
                $this->cacheRepoWrite->deleteMulti($cacheKeys);
            } else {
                $this->cacheRepoWrite->expireMulti(
                    $cacheKeys,
                    $this->cacheDeleteExpireTtl
                );
            }
        }
    }
