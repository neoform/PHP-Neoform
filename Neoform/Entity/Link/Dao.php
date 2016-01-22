<?php

    namespace Neoform\Entity\Link;

    use Neoform\Entity;
    use Neoform;

    /**
     * Link DAO Standard database access, for accessing tables that do not have a single primary key but instead a
     * composite key (2 column PK) that link two other tables together.
     *
     * It is strongly discouraged to include any other fields in this record type, as it breaks the convention of a
     * linking table. If you must have a linking record with additional fields, use a record entity instead.
     */
    abstract class Dao extends Entity\Dao implements Neoform\Entity\Link\Entity {

        /**
         * @var Entity\Repo\LinkSource\Driver
         */
        protected $sourceEngine;

        /**
         * @var int
         */
        protected $sourceEngineTtl;

        /**
         * Construct
         *
         * @param Neoform\Entity\Config\Overridden $config
         */
        public function __construct(Neoform\Entity\Config\Overridden $config) {
            parent::__construct($config);

            $this->sourceEngine = Neoform\Entity\Repo\LinkSource\Lib::getRepo(
                $this,
                $config->getSourceEngine(),
                $config->getSourceEnginePoolRead(),
                $config->getSourceEnginePoolWrite()
            );

            $this->sourceEngineTtl = $config->getSourceEngineTtl();
        }

        /**
         * Build a cache key used by the cache engine by combining the dao class name, the cache key and the variables found in the $params
         *
         * @param string       $cacheKeyName word used to identify this cache entry, it should be unique to the dao class its found in
         * @param string       $selectField
         * @param array        $orderBy       optional - array of order bys
         * @param integer      $offset         what starting position to get records from
         * @param integer|null $limit          how many records to select
         * @param array        $fieldVals      optional - array of table keys and their values being looked up in the table
         *
         * @return string a cache key that is unqiue to the application
         */
        final protected function _buildKeyLimit($cacheKeyName, $selectField, array $orderBy, $offset=null,
                                                         $limit=null, array $fieldVals=[]) {
            ksort($orderBy);

            // each key is namespaced with the name of the class, then the name of the function ($cacheKeyName)
            $paramCount = count($fieldVals);
            if ($paramCount === 1) {
                return static::getCacheKeyPrefix() . ":{$cacheKeyName}:{$selectField}:{$offset},{$limit}:" .
                    md5(json_encode($orderBy), $this->useBinaryCacheKeys) . ':' .
                    md5(reset($fieldVals) . ':' . key($fieldVals), $this->useBinaryCacheKeys);
            } else if ($paramCount === 0) {
                return static::getCacheKeyPrefix() . ":{$cacheKeyName}:{$selectField}:{$offset},{$limit}:" .
                    md5(json_encode($orderBy), $this->useBinaryCacheKeys) . ':';
            } else {
                ksort($fieldVals);
                foreach ($fieldVals as & $val) {
                    $val = base64_encode($val);
                }
                
                // Use only the array_values() and not the named array, since each $cacheKeyName is unique per function
                return static::getCacheKeyPrefix() . ":{$cacheKeyName}:{$selectField}:{$offset},{$limit}:" .
                    md5(json_encode($orderBy), $this->useBinaryCacheKeys) . ':' .
                    md5(json_encode($fieldVals), $this->useBinaryCacheKeys);
            }
        }

        /**
         * Gets fields that match the $keys, this gets the columns specified by $selectFields
         *
         * @param string $cacheKeyName word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array  $selectFields  array of table fields (table columns) to be selected
         * @param array  $fieldVals      array of table keys and their values being looked up in the table
         * @param array  $orderBy
         * @param null   $offset
         * @param null   $limit
         *
         * @return array  array of records from cache
         * @throws Entity\Repo\Exception
         */
        final protected function _byFields($cacheKeyName, array $selectFields, array $fieldVals, array $orderBy=null,
                                           $offset=null, $limit=null) {

            if ($orderBy) {
                $selectField = reset($selectFields);
                $limit       = $limit === null ? null : (int) $limit;
                $offset      = $offset === null ? null : (int) $offset;

                if (! isset($this->referencedEntities[$selectField])) {
                    throw new Entity\Repo\Exception("Unknown foreign key field \"{$selectField}\" in " . $this::getCacheKeyPrefix() . '.');
                }

                $foreignDao = Dao::dao($this->referencedEntities[$selectField]);

                $cacheKey = $this->_buildKeyLimit(
                    $cacheKeyName,
                    $selectField,
                    $orderBy,
                    $offset,
                    $limit,
                    $fieldVals
                );

                return $this->cacheRepo->single(
                    $cacheKey,
                    function() use ($selectField, $fieldVals, $foreignDao, $orderBy, $offset, $limit) {
                        return $this->sourceEngine->byFieldsLimit(
                            $selectField,
                            $foreignDao,
                            $fieldVals,
                            $orderBy,
                            $offset,
                            $limit
                        );
                    },
                    function($cacheKey, $results) use ($selectField, $fieldVals, $orderBy, $foreignDao) {

                        // The PKs found in this result set must also be put in meta cache to handle record deletion/updates
                        if (array_key_exists($selectField, $fieldVals)) {
                            $fieldVals[$selectField] = array_unique(array_merge(
                                is_array($fieldVals[$selectField]) ? $fieldVals[$selectField] : [ $fieldVals[$selectField] ],
                                $results
                            ));
                        } else {
                            $fieldVals[$selectField] = $results;
                        }

                        // Local DAO
                        $this->_setMetaCache($cacheKey, $fieldVals, [ $selectField ]);

                        // Foreign DAO
                        $orderBy[$foreignDao::getPrimaryKeyName()] = true; // add primary key to the list of fields
                        $foreignDao->_setMetaCache($cacheKey, null, array_keys($orderBy));
                    }
                );
            } else {
                return $this->cacheRepo->single(
                    $this->_buildKey($cacheKeyName, $fieldVals),
                    function() use ($selectFields, $fieldVals) {
                        return $this->sourceEngine->byFields(
                            $selectFields,
                            $fieldVals
                        );
                    },
                    function($cacheKey, $results) use ($selectFields, $fieldVals) {

                        /**
                         * In order to be more efficient, we do not want just clear all cache associated with $selectFields
                         * but instead, the data that has been returned from source. This means less cache busting.
                         */
                        foreach ($selectFields as $selectField) {
                            if (array_key_exists($selectField, $fieldVals)) {
                                $fieldVals[$selectField] = array_unique(array_merge(
                                    is_array($fieldVals[$selectField]) ? $fieldVals[$selectField] : [ $fieldVals[$selectField] ],
                                    array_column($results, $selectField)
                                ));
                            } else {
                                $fieldVals[$selectField] = array_column($results, $selectField);
                            }
                        }

                        $this->_setMetaCache($cacheKey, $fieldVals);
                    }
                );
            }
        }

        /**
         * Gets the ids of more than one set of key values
         *
         * @param string       $cacheKeyName word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array        $selectFields  array of table fields (table columns) to be selected
         * @param array        $keysArr       array of arrays of table keys and their values being looked up in the table - each sub-array must have matching keys
         * @param array        $orderBy       fields in the foreign table - key = field, val = order direction
         * @param integer|null $offset
         * @param integer|null $limit
         *
         * @return array  ids of records from cache
         * @throws Entity\Repo\Exception
         */
        final protected function _byFieldsMulti($cacheKeyName, array $selectFields, array $keysArr, array $orderBy=null,
                                                  $offset=null, $limit=null) {
            if ($orderBy) {
                // Limit ranges only work on single keys
                $selectField = reset($selectFields);
                $limit       = $limit === null ? null : (int) $limit;
                $offset      = $offset === null ? null : (int) $offset;

                if (! isset($this->referencedEntities[$selectField])) {
                    throw new Entity\Repo\Exception("Unknown foreign key field \"{$selectField}\" in " . $this::getNamespace() . '.');
                }

                $foreignDao = Dao::dao($this->referencedEntities[$selectField]);

                return $this->cacheRepo->multi(
                    $keysArr,
                    function($fieldVals) use ($cacheKeyName, $selectField, $orderBy, $limit, $offset) {
                        return $this->_buildKeyLimit(
                            $cacheKeyName,
                            $selectField,
                            $orderBy,
                            $offset,
                            $limit,
                            $fieldVals
                        );
                    },
                    function(array $fieldValsArr) use ($selectField, $foreignDao, $orderBy, $offset, $limit) {
                        return $this->sourceEngine->byFieldsLimitMulti(
                            $selectField,
                            $foreignDao,
                            $fieldValsArr,
                            $orderBy,
                            $offset,
                            $limit
                        );
                    },
                    function(array $cacheKeys, array $fieldValsArr, array $resultsArr) use ($selectField, $orderBy, $foreignDao) {

                        $cacheKeysFieldVals = [];
                        foreach ($cacheKeys as $k => $cacheKey) {

                            // The PKs found in this result set must also be put in meta cache to handle record deletion/updates
                            $fieldVals = & $fieldValsArr[$k];

                            if (array_key_exists($selectField, $fieldVals)) {
                                $fieldVals[$selectField] = array_unique(array_merge(
                                    is_array($fieldVals[$selectField]) ? $fieldVals[$selectField] : [ $fieldVals[$selectField] ],
                                    array_column($resultsArr[$k], $selectField)
                                ));
                            } else {
                                $fieldVals[$selectField] = array_column($resultsArr[$k], $selectField);
                            }

                            $cacheKeysFieldVals[$cacheKey] = $fieldVals;
                        }

                        // Local DAO
                        $this->_setMetaCacheMulti($cacheKeysFieldVals, [ $selectField ]);

                        // Foreign DAO
                        $orderBy[$foreignDao::getPrimaryKeyName()] = true; // add primary key to the list of fields

                        $foreignDao->_setMetaCacheMulti(array_flip($cacheKeys), array_keys($orderBy));
                    }
                );
            } else {
                return $this->cacheRepo->multi(
                    $keysArr,
                    function($fieldVals) use ($cacheKeyName) {
                        return $this->_buildKey($cacheKeyName, $fieldVals);
                    },
                    function(array $fieldValsArr) use ($selectFields) {
                        return $this->sourceEngine->byFieldsMulti($selectFields, $fieldValsArr);
                    },
                    function(array $cacheKeys, array $fieldValsArr, array $resultsArr) use ($selectFields) {

                        $cacheKeysFieldVals = [];
                        foreach ($cacheKeys as $k => $cacheKey) {

                            /**
                             * In order to be more efficient, we do not want just clear all cache associated with $selectFields
                             * but instead, the data that has been returned from source. This means less cache busting.
                             */
                            $fieldVals = & $fieldValsArr[$k];

                            foreach ($selectFields as $selectField) {
                                if (array_key_exists($selectField, $fieldVals)) {
                                    $fieldVals[$selectField] = array_unique(array_merge(
                                        is_array($fieldVals[$selectField]) ? $fieldVals[$selectField] : [ $fieldVals[$selectField] ],
                                        array_column($resultsArr[$k], $selectField)
                                    ));
                                } else {
                                    $fieldVals[$selectField] = array_column($resultsArr[$k], $selectField);
                                }
                            }

                            $cacheKeysFieldVals[$cacheKey] = $fieldVals;
                        }

                        $this->_setMetaCacheMulti($cacheKeysFieldVals, $selectFields);
                    }
                );
            }
        }

        /**
         * Get a record count
         *
         * @param array|null $fieldVals
         *
         * @return integer
         */
        public function count(array $fieldVals=null) {

            if ($fieldVals) {
                $this->bindFields($fieldVals);
            }

            return $this->cacheRepo->single(
                $this->_buildKey(parent::COUNT, $fieldVals ?: []),
                function() use ($fieldVals) {
                    return $this->sourceEngine->count($fieldVals);
                },
                function($cacheKey) use ($fieldVals) {
                    $this->_setMetaCache($cacheKey, $fieldVals);
                }
            );
        }

        /**
         * Get multiple record count
         *
         * @param array $fieldValsArr
         *
         * @return array
         */
        public function countMulti(array $fieldValsArr) {

            foreach ($fieldValsArr as $fieldVals) {
                $this->bindFields($fieldVals);
            }

            return $this->cacheRepo->multi(
                $fieldValsArr,
                function($fieldVals) {
                    return $this->_buildKey($this::COUNT, $fieldVals ?: []);
                },
                function(array $fieldValsArr) {
                    return $this->sourceEngine->countMulti($fieldValsArr);
                },
                function(array $cacheKeys, array $fieldValsArr, array $resultsArr) {
                    // Can't use array_combine since the keys might not be in the same order (possibly)
                    $cacheKeysFieldVals = [];
                    foreach ($cacheKeys as $k => $cacheKey) {
                        $cacheKeysFieldVals[$cacheKey] = $fieldValsArr[$k];
                    }
                    $this->_setMetaCacheMulti($cacheKeysFieldVals);
                }
            );
        }

        /**
         * Inserts a linking record into the database
         *
         * @param array   $info    an associative array of into to be put info the database
         * @param boolean $replace optional - user REPLACE INTO instead of INSERT INTO
         *
         * @return boolean
         * @throws Entity\Repo\Exception
         */
        protected function _insert(array $info, $replace=false) {
            try {
                $info = $this->sourceEngine->insert($info, $replace);
            } catch (Entity\Repo\Exception $e) {
                return false;
            }

            $this->_deleteMetaCache($info);

            return true;
        }

        /**
         * Inserts more than one linking record into the database at a time
         *
         * @access protected
         * @param array   $infos   an array of associative array of info to be put into the database
         * @param boolean $replace optional - user REPLACE INTO instead of INSERT INTO
         *
         * @return boolean
         * @throws Entity\Repo\Exception
         */
        protected function _insertMulti(array $infos, $replace=false) {
            if (! $infos) {
                return false;
            }

            try {
                $infos = $this->sourceEngine->insertMulti($infos, $replace);
            } catch (Entity\Repo\Exception $e) {
                return false;
            }

            $this->_deleteMetaCacheMulti($infos);

            return true;
        }

        /**
         * Updates linking records in the database
         *
         * @param array $newInfo the new info to be put into the model
         * @param array $where    return a model of the new record
         *
         * @return boolean
         * @throws Entity\Repo\Exception
         */
        protected function _update(array $newInfo, array $where) {
            if ($newInfo) {
                try {
                    $this->sourceEngine->update($newInfo, $where);
                } catch (Entity\Repo\Exception $e) {
                    return false;
                }

                // Delete any cache relating to the $newInfo or the $where
                $this->_deleteMetaCache(array_merge_recursive($newInfo, $where));

                return true;
            }

            return false;
        }

        /**
         * Delete linking records from the database
         *
         * @param array $keys the where of the query
         *
         * @return boolean
         * @throws Entity\Repo\Exception
         */
        protected function _delete(array $keys) {

            /**
             * Make sure the array has the correct number of keys, or the delete might corrupt the cache pool
             * by deleting records invisibly (eg, delete all records with field X = 5, which trashes Y=6, Y=7, Y=8
             * meanwhile those Y cache keys did not get busted.
             */
            if (count($keys) !== count($this->fieldBindings)) {
                throw new Entity\Repo\Exception(
                    'Link deletes must include all table fields (' . join(', ', array_keys($this->fieldBindings)) . ')'
                );
            }

            try {
                $this->sourceEngine->delete($keys);
            } catch (Entity\Repo\Exception $e) {
                return false;
            }

            $this->_deleteMetaCache($keys);

            return true;
        }

        /**
         * Delete linking records from the database
         *
         * @param array $keysArr arrays matching the PKs of the link
         *
         * @return boolean returns true on success
         * @throws Entity\Repo\Exception
         */
        protected function _deleteMulti(array $keysArr) {

            /**
             * Make sure the array has the correct number of keys, or the delete might corrupt the cache pool
             * by deleting records invisibly (eg, delete all records with field X = 5, which trashes Y=6, Y=7, Y=8
             * meanwhile those Y cache keys did not get busted.
             */
            $fieldCount = count($this->fieldBindings);
            foreach ($keysArr as $keys) {
                if (count($keys) !== $fieldCount) {
                    throw new Entity\Repo\Exception(
                        "Link deletes must include all table fields (" . join(', ', array_keys($this->fieldBindings)) . ')'
                    );
                }
            }

            try {
                $this->sourceEngine->deleteMulti($keysArr);
            } catch (Entity\Repo\Exception $e) {
                return false;
            }

            $this->_deleteMetaCacheMulti($keysArr);

            return true;
        }
    }
