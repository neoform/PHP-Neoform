<?php

    namespace Neoform\Entity\Record;

    use Neoform\Entity\Exception;
    use Neoform;

    /**
     * Entity\Record\Dao Standard database access, each extended DAO class must have a corresponding table with a primary key
     *
     * DAO class allows an entity to be looked up the same way as a regular DAO record, however it has the additional
     * ability to limit and order the lists or PKs that get looked up. Because the cache must always be accurate/fresh
     * lists of meta-data must be stored for each result set in order to identify which result sets (in cache) to destroy
     * when a record's field is updated.
     *
     * Eg, if a user's email address has changed, we must destroy any list of users that were 'order by email address ASC'
     * since that list is possibly no longer accurate. Also, since the result sets use limit/offsets, there are often
     * more than on result set cached away, so *all* cached result sets that used email to be ordered, most be destroyed.
     */
    abstract class Dao extends Neoform\Entity\Dao {

        /**
         * @var bool
         */
        protected $binaryPk;

        /**
         * @var Neoform\Entity\Repo\RecordSource\Driver
         */
        protected $sourceEngine;

        /**
         * @var int
         */
        protected $sourceEngineTtl;

        // Key name used for primary key lookups
        const RECORD = 'record';

        // All records
        const ALL = 'all';

        // Generic orderby/limit/offset (with no WHERE)
        const LIMIT = 'limit';

        /**
         * Construct
         *
         * @param Neoform\Entity\Config\Overridden $config
         */
        public function __construct(Neoform\Entity\Config\Overridden $config) {
            parent::__construct($config);

            $this->sourceEngine = Neoform\Entity\Repo\RecordSource\Lib::getRepo(
                $this,
                $config->getSourceEngine(),
                $config->getSourceEnginePoolRead(),
                $config->getSourceEnginePoolWrite()
            );

            $this->sourceEngineTtl = $config->getSourceEngineTtl();
            $this->binaryPk        = $this->fieldBindings[static::PRIMARY_KEY] === parent::TYPE_BINARY;
        }

        /**
         * Build a cache key used by the cache engine by combining the dao class name, the cache key and the variables found in the $params
         *
         * @param string       $cacheKeyName word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array        $orderBy       optional - array of order bys
         * @param integer|null $offset         what starting position to get records from
         * @param integer|null $limit          how many records to select
         * @param array        $params         optional - array of table keys and their values being looked up in the table
         *
         * @return string a cache key that is unqiue to the application
         */
        final protected function _buildKeyLimit($cacheKeyName, array $orderBy, $offset=null, $limit=null, array $params=[]) {
            ksort($orderBy);

            // each key is namespaced with the name of the class, then the name of the function ($cacheKeyName)
            $paramCount = count($params);

            // To speed up key creation, we have different ways of handling simpler keys that have fewer params
            if ($paramCount === 1) {
                return static::CACHE_KEY . ":{$cacheKeyName}:{$offset},{$limit}:" .
                    md5(json_encode($orderBy), $this->useBinaryCacheKeys) . ':' .
                    md5(reset($params) . ':' . key($params), $this->useBinaryCacheKeys);
            } else if ($paramCount === 0) {
                return static::CACHE_KEY . ":{$cacheKeyName}:{$offset},{$limit}:" .
                    md5(json_encode($orderBy), $this->useBinaryCacheKeys) . ':';
            } else {
                ksort($params);
                foreach ($params as & $param) {
                    $param = base64_encode($param);
                }

                // Use only the array_values() and not the named array, since each $cacheKeyName is unique per function
                return static::CACHE_KEY . ":{$cacheKeyName}:{$offset},{$limit}:" .
                    md5(json_encode($orderBy), $this->useBinaryCacheKeys) . ':' .
                    md5(json_encode($params), $this->useBinaryCacheKeys);
            }
        }

        /**
         * I wish we had inline functions in PHP, this would be a great candidate for one
         *
         * @param int|string $pk
         *
         * @return string
         */
        final protected function _buildKeyRecord($pk) {
            return static::CACHE_KEY . ':' . self::RECORD . ':' . ($this->binaryPk ? md5($pk, $this->useBinaryCacheKeys) : $pk);
        }

        /**
         * Pulls a single record's information from the database
         *
         * @param int $pk primary key of a record
         *
         * @return array cached record data
         * @throws Exception
         */
        public function record($pk) {
            return $this->cacheRepo->single(
                $this->_buildKeyRecord($pk),
                function() use ($pk) {
                    return $this->sourceEngine->record($pk);
                }
            );
        }

        /**
         * Pulls a single record's information from the database
         *
         * @param array  $pks primary key of a records
         *
         * @return array cached records data - with preserved key names from $pks.
         * @throws Exception
         */
        public function records(array $pks) {

            if (! $pks) {
                return [];
            }

            return $this->cacheRepo->multi(
                $pks,
                function($pk)  {
                    return $this->_buildKeyRecord($pk);
                },
                function(array $pks) {
                    return $this->sourceEngine->records($pks);
                }
            );
        }

        /**
         * Get list of PKs, ordered and limited
         *
         * @param array $orderBy array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of User ids
         */
        public function limit(array $orderBy=null, $offset=null, $limit=null) {
            return $this->_byFields(
                self::LIMIT,
                [],
                $orderBy,
                $offset,
                $limit
            );
        }

        /**
         * Get multiple list of PKs, ordered and limited
         *
         * @param array $orderBy array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of User ids
         */
        public function limit_multi(array $orderBy=null, $offset=null, $limit=null) {
            return $this->_byFieldsMulti(
                self::LIMIT,
                [],
                $orderBy,
                $offset,
                $limit
            );
        }

        /**
         * Gets the full record(s) that match the $fieldVals
         *
         * @param array $fieldVals array of table keys and their values being looked up in the table
         *
         * @return array pks of records from cache
         * @throws Exception
         */
        public function all(array $fieldVals=null) {

            if ($fieldVals) {
                $this->bindFields($fieldVals);
            }

            return $this->cacheRepo->single(
                $this->_buildKey(self::ALL),
                function() use ($fieldVals) {
                    return $this->sourceEngine->all($fieldVals);
                },
                function($cacheKey) {
                    $this->_setMetaCache($cacheKey);
                }
            );
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
                function(array $cacheKeys, array $fieldValsArr, array $pkResultsArr) {
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
         * Gets the primary keys of records that match the $fieldVals
         *
         * @param string  $cacheKeyName word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $fieldVals      array of fields/values being looked up in the table
         * @param array   $orderBy       array of fields to order by - key = field, val = order direction
         * @param integer $offset         records starting at what offset
         * @param integer $limit          max number of record to return
         *
         * @return mixed
         */
        final protected function _byFields($cacheKeyName, array $fieldVals, array $orderBy=null, $offset=null, $limit=null) {
            if ($orderBy) {
                $limit  = $limit === null ? null : (int) $limit;
                $offset = $offset === null ? null : (int) $offset;

                $cacheKey = $this->_buildKeyLimit(
                    $cacheKeyName,
                    $orderBy,
                    $offset,
                    $limit,
                    $fieldVals
                );

                return $this->cacheRepo->single(
                    $cacheKey,
                    function() use ($cacheKey, $fieldVals, $orderBy, $offset, $limit) {
                        return $this->sourceEngine->byFieldsOffset(
                            $fieldVals,
                            $orderBy,
                            $offset,
                            $limit
                        );
                    },
                    function($cacheKey, $pks) use ($fieldVals, $orderBy) {

                        if (array_key_exists($this::PRIMARY_KEY, $fieldVals)) {
                            $fieldVals[$this::PRIMARY_KEY] = array_unique(array_merge(
                                is_array($fieldVals[$this::PRIMARY_KEY]) ? $fieldVals[$this::PRIMARY_KEY] : [ $fieldVals[$this::PRIMARY_KEY] ],
                                $pks
                            ));
                        } else {
                            $fieldVals[$this::PRIMARY_KEY] = $pks;
                        }

                        $this->_setMetaCache($cacheKey, $fieldVals, array_keys($orderBy));
                    }
                );
            } else {
                return $this->cacheRepo->single(
                    $this->_buildKey($cacheKeyName, $fieldVals),
                    function() use ($fieldVals) {
                        return $this->sourceEngine->byFields($fieldVals);
                    },
                    function($cacheKey, $pks) use ($fieldVals) {

                        $pk = $this::PRIMARY_KEY;

                        // The PKs found in this result set must also be put in meta cache to handle record deletion/updates
                        if (array_key_exists($pk, $fieldVals)) {
                            $fieldVals[$pk] = array_unique(array_merge(
                                is_array($fieldVals[$pk]) ? $fieldVals[$pk] : [ $fieldVals[$pk] ],
                                $pks
                            ));
                        } else {
                            $fieldVals[$pk] = $pks;
                        }

                        $this->_setMetaCache($cacheKey, $fieldVals);
                    }
                );
            }
        }

        /**
         * Gets the pks of more than one set of key values
         *
         * @param string  $cacheKeyName word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $fieldValsArr       array of arrays of table keys and their values being looked up in the table - each sub-array must have matching keys
         * @param array   $orderBy       array of fields to order by - key = field, val = order direction
         * @param integer $offset         records starting at what offset
         * @param integer $limit          max number of record to return
         *
         * @return array  pks of records from cache
         * @throws Exception
         */
        final protected function _byFieldsMulti($cacheKeyName, array $fieldValsArr, array $orderBy=null, $offset=null, $limit=null) {
            if ($orderBy) {
                $limit  = $limit === null ? null : (int) $limit;
                $offset = $offset === null ? null : (int) $offset;

                return $this->cacheRepo->multi(
                    $fieldValsArr,
                    function($fieldVals) use ($cacheKeyName, $orderBy, $offset, $limit) {
                        return $this->_buildKeyLimit(
                            $cacheKeyName,
                            $orderBy,
                            $offset,
                            $limit,
                            $fieldVals
                        );
                    },
                    function(array $fieldValsArr) use ($orderBy, $offset, $limit) {
                        return $this->sourceEngine->byFieldsOffsetMulti(
                            $fieldValsArr,
                            $orderBy,
                            $offset,
                            $limit
                        );
                    },
                    function(array $cacheKeys, array $fieldValsArr, array $pkResultsArr) use ($orderBy) {

                        $pk = $this::PRIMARY_KEY;

                        $cacheKeysFieldVals = [];
                        foreach ($cacheKeys as $k => $cacheKey) {

                            // The PKs found in this result set must also be put in meta cache to handle record deletion/updates
                            $fieldVals = & $fieldValsArr[$k];

                            if (array_key_exists($pk, $fieldVals)) {
                                $fieldVals[$pk] = array_unique(array_merge(
                                    is_array($fieldVals[$pk]) ? $fieldVals[$pk] : [ $fieldVals[$pk] ],
                                    $pkResultsArr[$k]
                                ));
                            } else {
                                $fieldVals[$pk] = $pkResultsArr[$k];
                            }

                            $cacheKeysFieldVals[$cacheKey] = $fieldVals;
                        }

                        $this->_setMetaCacheMulti($cacheKeysFieldVals, array_keys($orderBy));
                    }
                );
            } else {
                return $this->cacheRepo->multi(
                    $fieldValsArr,
                    function($fieldVals) use ($cacheKeyName) {
                        return $this->_buildKey($cacheKeyName, $fieldVals);
                    },
                    function(array $fieldValsArr) {
                        return $this->sourceEngine->byFieldsMulti($fieldValsArr);
                    },
                    function(array $cacheKeys, array $fieldValsArr, array $pkResultsArr) {
                        $pk = $this::PRIMARY_KEY;

                        $cacheKeysFieldVals = [];
                        foreach ($cacheKeys as $k => $cacheKey) {

                            // The PKs found in this result set must also be put in meta cache to handle record deletion/updates
                            $fieldVals = & $fieldValsArr[$k];

                            if (array_key_exists($pk, $fieldVals)) {
                                $fieldVals[$pk] = array_unique(array_merge(
                                    is_array($fieldVals[$pk]) ? $fieldVals[$pk] : [ $fieldVals[$pk] ],
                                    $pkResultsArr[$k]
                                ));
                            } else {
                                $fieldVals[$pk] = $pkResultsArr[$k];
                            }

                            $cacheKeysFieldVals[$cacheKey] = $fieldVals;
                        }

                        $this->_setMetaCacheMulti($cacheKeysFieldVals);
                    }
                );
            }
        }

        /**
         * Inserts a record into the database
         *
         * @param array   $info                   an associative array of into to be put into the database
         * @param boolean $replace                optional - user REPLACE INTO instead of INSERT INTO
         * @param boolean $returnModel           optional - return a model of the new record
         * @param boolean $loadModelFromSource optional - after insert, load data from source - this is needed if the DB changes values on insert (eg, timestamps)
         *
         * @return model|boolean if $returnModel is set to true, the model created from the info is returned
         */
        protected function _insert(array $info, $replace=false, $returnModel=true, $loadModelFromSource=false) {

            try {
                $info = $this->sourceEngine->insert(
                    $info,
                    $replace,
                    $this->sourceEngineTtl,
                    $loadModelFromSource
                );
            } catch (Exception $e) {
                return false;
            }

            // In case a blank record was cached
            $this->cacheRepoWrite->set(
                $this->_buildKeyRecord($info[static::PRIMARY_KEY]),
                $info
            );

            $this->_deleteMetaCache($info);

            if ($returnModel) {
                $model = '\\' . static::ENTITY_NAME . '\\Model';
                return new $model(null, $info);
            } else if ($loadModelFromSource) {
                return $info;
            }

            return true;
        }

        /**
         * Inserts multiple record into the database
         *
         * @param array   $infos                 an array of associative arrays of into to be put into the database, if this dao represents multiple tables, the info will be split up across the applicable tables.
         * @param boolean $keysMatch            optional - if all the records being inserted have the same array keys this should be true. it is faster to insert all the records at the same time, but this can only be done if they all have the same keys.
         * @param boolean $replace               optional - user REPLACE INTO instead of INSERT INTO
         * @param boolean $returnCollection      optional - return a collection of models created
         * @param boolean $loadModelsFromSource  optional - after insert, load data from source - this is needed if the DB changes values on insert (eg, timestamps)
         *
         * @return Collection|boolean if $returnCollection is true function returns a collection
         * @throws Exception
         */
        protected function _insertMulti(array $infos, $keysMatch=true, $replace=false, $returnCollection=true,
                                    $loadModelsFromSource=false) {
            try {
                $infos = $this->sourceEngine->insertMulti(
                    $infos,
                    $keysMatch,
                    $replace,
                    $this->sourceEngineTtl,
                    $loadModelsFromSource
                );
            } catch (Exception $e) {
                return false;
            }

            $insert_cache_data = [];
            foreach ($infos as $info) {
                $insert_cache_data[$this->_buildKeyRecord($info[static::PRIMARY_KEY])] = $info;
            }

            $this->cacheRepoWrite->setMulti($insert_cache_data);

            $this->_deleteMetaCacheMulti($infos);

            if ($returnCollection) {
                $collection = '\\' . static::ENTITY_NAME . '\\Collection';
                return new $collection(null, $infos);
            } else if ($loadModelsFromSource) {
                return $infos;
            }

            return true;
        }

        /**
         * Updates a record in the database
         *
         * @param Model   $model               the model that is to be updated
         * @param array   $newInfo             the new info to be put into the model
         * @param boolean $returnModel         optional - return a model of the new record
         * @param boolean $loadModelFromSource optional - after update, load data from source - this is needed if the DB changes values on update (eg, timestamps)
         *
         * @return Model|bool                  if $returnModel is true, an updated model is returned
         * @throws Exception
         */
        protected function _update(Model $model, array $newInfo, $returnModel=true, $loadModelFromSource=false) {

            if (! $newInfo) {
                return $returnModel ? $model : false;
            }

            /**
             * Filter out any fields that have not actually changed - no point in updating the record and destroying
             * cache if nothing actually changed
             */
            $oldInfo = $model->export();
            $newInfo = array_intersect_key($newInfo, $this->fieldBindings);

            if (! $newInfo) {
                return $returnModel ? $model : false;
            }

            $pk = static::PRIMARY_KEY;

            try {
                $reloadedInfo = $this->sourceEngine->update(
                    $model,
                    $newInfo,
                    $this->sourceEngineTtl,
                    $loadModelFromSource
                );
                
            } catch (Exception $e) {
                return false;
            }

            /**
             * Reload model from source based on current (or newly updated) PK
             * We reload it in case there were any fields updated by an external source during the process (such as a timestamp)
             */
            if ($loadModelFromSource) {
                $newInfo = $reloadedInfo;
            }

            $this->cacheRepoWrite->batchStart();

            /**
             * If the primary key was changed, bust the cache for that new key too
             * technically the PK should never change though... that kinda defeats the purpose of a record PK...
             */
            if (array_key_exists($pk, $newInfo)) {
                // Set the cache record
                $this->cacheRepoWrite->set(
                    $this->_buildKeyRecord($newInfo[$pk]),
                    $newInfo + $oldInfo
                );

                // Destroy the old key
                if ($this->cacheReposAreTheSame) {
                    $this->cacheRepoWrite->delete(
                        $this->_buildKeyRecord($model->$pk)
                    );
                } else {
                    $this->cacheRepoWrite->expire(
                        $this->_buildKeyRecord($model->$pk),
                        $this->cacheDeleteExpireTtl
                    );
                }
            } else {
                // Update cache record
                $this->cacheRepoWrite->set(
                    $this->_buildKeyRecord($model->$pk),
                    $newInfo + $oldInfo
                );
            }

            $this->cacheRepoWrite->batchExecute();

            /**
             * Destroy cache based on the fields that were changed
             * Do not wrap this function in a batch execution since it does a listPull() on the meta cache
             */
            $this->_deleteMetaCache(
                $this->arrayDifferences(
                    $newInfo,
                    $oldInfo
                )
            );

            if ($returnModel) {
                if ($loadModelFromSource) {
                    return new $model(null, $newInfo);
                } else {
                    $updated_model = clone $model;
                    $updated_model->_update($newInfo);
                    return $updated_model;
                }
            } else {
                return true;
            }
        }

        /**
         * Deletes a record from the database
         *
         * @param Model $model the model that is to be deleted
         *
         * @return boolean returns true on success
         * @throws Exception
         */
        protected function _delete(Model $model) {

            $pk = static::PRIMARY_KEY;

            try {
                $this->sourceEngine->delete($model);
            } catch (Exception $e) {
                return false;
            }

            if ($this->cacheReposAreTheSame) {
                $this->cacheRepoWrite->delete(
                    $this->_buildKeyRecord($model->$pk)
                );
            } else {
                $this->cacheRepoWrite->expire(
                    $this->_buildKeyRecord($model->$pk),
                    $this->cacheDeleteExpireTtl
                );
            }

            // Destroy cache based on table fieldvals - do not wrap this function in a batch execution
            $this->_deleteMetaCache($model->export());

            return true;
        }

        /**
         * Deletes a record from the database
         *
         * @param collection $collection the collection of models that is to be deleted
         *
         * @return boolean|null returns true on success
         * @throws Exception
         */
        protected function _deleteMulti(Collection $collection) {

            if (! count($collection)) {
                return null;
            }

            try {
                $this->sourceEngine->deleteMulti($collection);
            } catch (Exception $e) {
                return false;
            }

            $deleteCacheKeys = [];
            foreach ($collection->field(static::PRIMARY_KEY) as $pk) {
                $deleteCacheKeys[] = $this->_buildKeyRecord($pk);
            }

            if ($this->cacheReposAreTheSame) {
                $this->cacheRepoWrite->deleteMulti(
                    $deleteCacheKeys
                );
            } else {
                $this->cacheRepoWrite->expireMulti(
                    $deleteCacheKeys,
                    $this->cacheDeleteExpireTtl
                );
            }

            // Destroy cache based on table fieldvals - do not wrap this function in a batch execution
            $collectionData           = $collection->export();
            $collectionDataOrganized = [];
            foreach (array_keys(reset($collectionData)) as $field) {
                $collectionDataOrganized[$field] = array_column($collectionData, $field);
            }

            $this->_deleteMetaCache($collectionDataOrganized);

            return true;
        }

        /**
         * Compare two arrays and return all the differences
         *
         * @param array $arr1
         * @param array $arr2
         *
         * @return array containing the differences, if two differences share the same key, an array is created with the two values
         */
        protected function arrayDifferences(array $arr1, array $arr2) {
            $diff = [];

            // Arr1
            foreach ($arr1 as $k => $v) {
                // Common Keys between the two arrays
                if (array_key_exists($k, $arr2)) {

                    // Vals are not the same
                    if ($v !== $arr2[$k]) {
                        $diff[$k] = [ $v, $arr2[$k], ];
                    }

                // Key/Val from arr1 doesn't exist in arr2
                } else {
                    $diff[$k] = $v;
                }
            }

            // Arr2
            foreach ($arr2 as $k => $v) {
                // Key/Val from arr2 doesn't exist in arr1
                if (! array_key_exists($k, $arr1)) {
                    $diff[$k] = $v;
                }
            }

            return $diff;
        }
    }
