<?php

    namespace Neoform\Entity\Record;

    use Neoform\Entity\Exception;
    use Neoform;

    /**
     * Record Collection
     */
    abstract class Collection
        extends Neoform\Entity\Collection
        implements Neoform\Entity\Record\Entity {

        /**
         * Get a collection by a given field or fields
         * folderCollection::byParent(5) will return a folder collection.
         * this is just a shortcut for folderCollection::fromPks(Dao::dao('Neoform\Folder')->byParent(5));
         *
         * @param string $name
         * @param array  $args
         *
         * @return Collection
         */
        public static function __callStatic($name, array $args) {
            $collectionClassName = '\\' . static::getNamespace() . '\\Collection';
            if ($name === 'all' || $name === 'records') {
                return $collectionClassName::fromArrays(
                    call_user_func_array(
                        [
                            Dao::dao(static::getNamespace()),
                            $name,
                        ],
                        $args
                    )
                );
            }

            return $collectionClassName::fromPks(
                call_user_func_array(
                    [
                        Dao::dao(static::getNamespace()),
                        $name,
                    ],
                    $args
                )
            );
        }

        /**
         * Create a collection an array of PKs
         *
         * @param array       $pks
         * @param string|null $mapField
         *
         * @return static
         */
        public static function fromPks(array $pks, $mapField=null) {

            if (! $pks) {
                return new static;
            }

            $infos = Dao::dao(static::getNamespace())->records($pks);

            if (! $infos) {
                return new static;
            }

            $collection     = new static;
            $modelClassName = '\\' . static::getNamespace() . '\\Model';

            foreach ($infos as $key => $info) {
                try {
                    if (is_array($info)) {
                        $collection->models[$mapField !== null ? $info[$mapField] : $key] = $modelClassName::fromArray($info);
                    }
                } catch (Exception $e) {

                }
            }

            return $collection;
        }

        /**
         * Create a collection from an array of info arrays
         *
         * @param array       $arrs
         * @param string|null $mapField
         *
         * @return static
         * @throws \Exception
         */
        public static function fromArrays(array $arrs, $mapField=null) {

            if (! $arrs) {
                return new static;
            }

            $collection     = new static;
            $modelClassName = '\\' . static::getNamespace() . '\\Model';

            foreach ($arrs as $key => $arr) {
                if (! is_array($arr)) {
                    throw new \Exception('fromArrays() only accepts arrays');
                }

                $collection->models[$mapField !== null ? $arr[$mapField] : $key] = $modelClassName::fromArray($arr);
            }

            return $collection;
        }

        /**
         * Create a collection from an array of models
         *
         * @param Model[]     $models
         * @param string|null $mapField
         *
         * @return static
         * @throws \Exception
         */
        public static function fromModels(array $models, $mapField=null) {

            if (! $models) {
                return new static;
            }

            $collection     = new static;
            $modelClassName = '\\' . static::getNamespace() . '\\Model';

            foreach ($models as $key => $model) {
                if (! ($model instanceof $modelClassName)) {
                    throw new \Exception('fromArrays() only accepts arrays');
                }

                $collection->models[$mapField !== null ? $model->get($mapField) : $key] = $model;
            }

            return $collection;
        }

        /**
         * Add a model to the collection
         *
         * @param Model $model
         * @param string|null $mapField Assign collection keys based on this field (taken from the models)
         *
         * @return $this
         */
        public function add($model, $mapField=null) {
            if ($mapField !== null) {
                $this->models[$model->get($mapField)] = $model;
            } else {
                $this->models[] = $model;
            }

            // Reset
            $this->_vars = [];

            return $this;
        }

        /**
         * Add a model to the collection by info array
         *
         * @param array $info
         * @param string|null $mapField Assign collection keys based on this field (taken from the models)
         *
         * @return $this
         */
        public function addByArray(array $info, $mapField=null) {
            $modelClassName = '\\' . static::getNamespace() . '\\Model';
            $model = $modelClassName::fromArray($info);

            if ($mapField !== null) {
                $this->models[$model->get($mapField)] = $model;
            } else {
                $this->models[] = $model;
            }

            //reset
            $this->_vars = [];

            return $this;
        }

        /**
         * Add a model to the collection by PK
         *
         * @param string|int $pk
         * @param string|null $mapField Assign collection keys based on this field (taken from the models)
         *
         * @return $this
         */
        public function addByPk($pk, $mapField=null) {
            $modelClassName = '\\' . static::getNamespace() . '\\Model';
            $model = $modelClassName::fromPk($pk);

            if ($mapField !== null) {
                $this->models[$model->get($mapField)] = $model;
            } else {
                $this->models[] = $model;
            }

            //reset
            $this->_vars = [];

            return $this;
        }

        /**
         * Remap the collection according to a certain field - this makes the key of the collection be that field
         *
         * @param string $field
         * @param bool   $ignoreNull
         *
         * @return Collection $this
         */
        public function remap($field, $ignoreNull=false) {
            $new = [];
            if ($ignoreNull) {
                foreach ($this->models as $model) {
                    if ($model->get($field) !== null) {
                        $new[$model->get($field)] = $model;
                    }
                }
            } else {
                foreach ($this->models as $model) {
                    $new[$model->get($field)] = $model;
                }
            }
            $this->models = $new;
            return $this;
        }

        /**
         * Get an array with the values of the models in the collection
         *
         * @param string      $field
         * @param string|null $key
         *
         * @return array
         */
        public function field($field, $key=null) {
            $arr = [];
            foreach ($this->models as $k => $model) {
                $arr[$key ? $model->get($key) : $k] = $model->get($field);
            }
            return $arr;
        }

        /**
         * Exports an array with all the data from the models, or select fields
         *
         * @param array|null $fields to export
         *
         * @return array
         */
        public function export(array $fields=null) {
            $arr = [];
            foreach ($this->models as $k => $model) {
                $arr[$k] = $model->export($fields);
            }
            return $arr;
        }

        /**
         * Get many groups of records all in one shot. This greatly reduces the number of requests to the cache service,
         * which can greatly speed up an application.
         *
         * @param string       $varKey    Key (in $model::$_var) that stores preloaded model data
         * @param string       $entity      eg, user
         * @param string       $by_function eg, by_comments
         * @param array|null   $order_by    array of field names (as the key) and sort direction (Neoform\Entity\Record\Dao::SORT_ASC, Neoform\Entity\Record\Dao::SORT_DESC)
         * @param integer|null $offset      get PKs starting at this offset
         * @param integer|null $limit       max number of PKs to return
         *
         * @return Collection
         * @deprecated
         */
        protected function _preload_one_to_many($varKey, $entity, $by_function, array $order_by=null, $offset=null,
                                                $limit=null) {

            $collection_name  = "\\{$entity}\\Collection";
            $model_name       = "\\{$entity}\\Model";
            $dao              = Dao::dao($entity);
            $by_function     .= '_multi';

            // Get the ids for those
            $pks_groups = $dao->$by_function($this, $order_by, $offset, $limit);
            $pks        = [];

            // make a flat array of all keys, removing dupes along the way.
            foreach ($pks_groups as $pks_group) {
                foreach ($pks_group as $pk) {
                    $pks[$pk] = $pk;
                }
            }

            // get all the records all in one shot
            $collection = $collection_name::fromPks($pks);

            // sort flat array back into grouped data again
            foreach ($pks_groups as & $pks_group) {
                foreach ($pks_group as $k => $pk) {
                    if (isset($collection[$pk])) {
                        $pks_group[$k] = $collection[$pk];
                    } else {
                        // this shouldn't actually happen... if it did, something went wrong in the DAO
                        unset($pks_group[$k]);
                    }
                }
            }

            // load the child models into the $_var of each model in this collection
            foreach ($this->models as $key => $model) {
                if (isset($pks_groups[$key])) {
                    $model->_preloadCache(
                        $model_name::_limitVarKey(
                            $varKey,
                            $order_by,
                            $offset,
                            $limit
                        ),
                        $collection_name::fromModels($pks_groups[$key])
                    );
                }
            }

            return $collection;
        }

        /**
         * Get many groups of records all in one shot. This greatly reduces the number of requests to the cache service,
         * which can greatly speed up an application.
         *
         * @param string       $varKey    Key (in $model::$_var) that stores preloaded model data
         * @param string       $entity     eg, user
         * @param string       $byFunction eg, by_comments
         * @param array|null   $orderBy    array of field names (as the key) and sort direction (Neoform\Entity\Record\Dao::SORT_ASC, Neoform\Entity\Record\Dao::SORT_DESC)
         * @param integer|null $offset     get PKs starting at this offset
         * @param integer|null $limit      max number of PKs to return
         *
         * @return Collection
         */
        protected function _preloadOneToMany($varKey, $entity, $byFunction, array $orderBy=null, $offset=null,
                                                $limit=null) {

            $collectionName = "\\{$entity}\\Collection";
            $modelName      = "\\{$entity}\\Model";
            $dao            = Dao::dao($entity);
            $byFunction    .= 'Multi';

            // Get the ids for those
            $pksGroups = $dao->{$byFunction}($this, $orderBy, $offset, $limit);
            $pks       = [];

            // make a flat array of all keys, removing dupes along the way.
            foreach ($pksGroups as $pksGroup) {
                foreach ($pksGroup as $pk) {
                    $pks[$pk] = $pk;
                }
            }

            // get all the records all in one shot
            $collection = $collectionName::fromArrays($dao->records($pks));

            // sort flat array back into grouped data again
            foreach ($pksGroups as & $pksGroup) {
                foreach ($pksGroup as $k => $pk) {
                    if (isset($collection[$pk])) {
                        $pksGroup[$k] = $collection[$pk];
                    } else {
                        // this shouldn't actually happen... if it did, something went wrong in the DAO
                        unset($pksGroup[$k]);
                    }
                }
            }

            // load the child models into the $_var of each model in this collection
            foreach ($this->models as $key => $model) {
                if (isset($pksGroups[$key])) {
                    $model->_preloadCache(
                        $modelName::_limitVarKey(
                            $varKey,
                            $orderBy,
                            $offset,
                            $limit
                        ),
                        $collectionName::fromModels($pksGroups[$key])
                    );
                }
            }

            return $collection;
        }

        /**
         * Get many groups of records all in one shot. This greatly reduces the number of requests to the cache service,
         * which can greatly speed up an application.
         *
         * @param string       $varKey       Key (in $model::$_var) that stores preloaded model data
         * @param string       $entity         eg, user_permission
         * @param string       $by_function    eg, by_user
         * @param string       $foreign_type   eg, permission
         * @param array|null   $orderBy       array of field names (as the key) and sort direction (Neoform\Entity\Record\Dao::SORT_ASC, Neoform\Entity\Record\Dao::SORT_DESC)
         * @param integer|null $offset         get PKs starting at this offset
         * @param integer|null $limit          max number of PKs to return
         *
         * @return Collection
         * @deprecated
         */
        protected function _preload_many_to_many($varKey, $entity, $by_function, $foreign_type,
                                                 array $orderBy=null, $offset=null, $limit=null) {

            $by_function              .= '_multi';
            $foreign_collection_name  = "\\{$foreign_type}\\Collection";

            // Get the ids for those
            $pks_groups = Dao::dao($entity)->$by_function($this, $orderBy, $offset, $limit);
            $pks        = [];

            // make a flat array of all keys, removing dupes along the way.
            foreach ($pks_groups as $pks_group) {
                foreach ($pks_group as $pk) {
                    $pks[$pk] = $pk;
                }
            }

            // get all the records all in one shot
            $collection = $foreign_collection_name::fromPks($pks);

            // sort flat array back into grouped data again
            foreach ($pks_groups as & $pks_group) {
                foreach ($pks_group as $k => $pk) {
                    if (isset($collection[$pk])) {
                        $pks_group[$k] = $collection[$pk];
                    } else {
                        // this shouldn't actually happen... if it did, something went wrong in the DAO
                        unset($pks_group[$k]);
                    }
                }
            }

            foreach ($this->models as $key => $model) {
                if (isset($pks_groups[$key])) {
                    $model->_preloadCache(
                        $varKey,
                        $foreign_collection_name::fromModels($pks_groups[$key])
                    );
                }
            }

            return $collection;
        }

        /**
         * Get many groups of records all in one shot. This greatly reduces the number of requests to the cache service,
         * which can greatly speed up an application.
         *
         * @param string     $varKey      Key (in $model::$_var) that stores preloaded model data
         * @param string     $entity      eg, user_permission
         * @param string     $byFunction  eg, by_user
         * @param string     $foreignType eg, permission
         * @param array|null $orderBy     array of field names (as the key) and sort direction (Neoform\Entity\Record\Dao::SORT_ASC, Neoform\Entity\Record\Dao::SORT_DESC)
         * @param int|null   $offset      get PKs starting at this offset
         * @param int|null   $limit       max number of PKs to return
         *
         * @return Collection
         */
        protected function _preloadManyToMany($varKey, $entity, $byFunction, $foreignType,
                                                 array $orderBy=null, $offset=null, $limit=null) {

            $byFunction            .= 'Multi';
            $foreignCollectionName  = "\\{$foreignType}\\Collection";

            // Get the ids for those
            $pksGroups = Dao::dao($entity)->{$byFunction}($this, $orderBy, $offset, $limit);
            $pks       = [];

            // make a flat array of all keys, removing dupes along the way.
            foreach ($pksGroups as $pksGroup) {
                foreach ($pksGroup as $pk) {
                    $pks[$pk] = $pk;
                }
            }

            // get all the records all in one shot
            $collection = $foreignCollectionName::fromPks($pks);

            // sort flat array back into grouped data again
            foreach ($pksGroups as & $pksGroup) {
                foreach ($pksGroup as $k => $pk) {
                    if (isset($collection[$pk])) {
                        $pks_group[$k] = $collection[$pk];
                    } else {
                        // this shouldn't actually happen... if it did, something went wrong in the DAO
                        unset($pksGroup[$k]);
                    }
                }
            }

            foreach ($this->models as $key => $model) {
                if (isset($pksGroups[$key])) {
                    $model->_preloadCache(
                        $varKey,
                        $foreignCollectionName::fromModels($pksGroups[$key])
                    );
                }
            }

            return $collection;
        }

        /**
         * Get many groups of records all in one shot. This greatly reduces the number of requests to the cache service,
         * which can greatly speed up an application.
         *
         * @param string $varKey Key (in $model::$_var) that stores preloaded model data
         * @param string $entity   Name of the entity
         * @param string $field    Corresponding field for that entity
         *
         * @return Collection
         * @deprecated
         */
        protected function _preload_one_to_one($varKey, $entity, $field) {

            $modelName      = "\\{$entity}\\Model";
            $collectionName = "\\{$entity}\\Collection";

            $pks = [];
            // we don't want to look up duplicates, just unique values
            foreach ($this->models as $model) {
                $pks[$model->get($field)] = $model->get($field);
            }
            $infos  = Dao::dao($entity)->records($pks);
            $models = [];

            foreach ($this->models as $key => $model) {
                $k = $model->get($field);
                if (isset($infos[$k])) {
                    $model->_preloadCache(
                        $varKey,
                        $models[$key] = $modelName::fromArray($infos[$k])
                    );
                }
            }

            return $collectionName::fromModels($models);
        }

        /**
         * Get many groups of records all in one shot. This greatly reduces the number of requests to the cache service,
         * which can greatly speed up an application.
         *
         * @param string $varKey                  Key (in $model::$_var) that stores preloaded model data
         * @param string $entity                  Name of the entity
         * @param string $fieldGetterFunctionName Corresponding field for that entity
         *
         * @return Collection
         */
        protected function _preloadOneToOne($varKey, $entity, $fieldGetterFunctionName) {

            $modelName      = "\\{$entity}\\Model";
            $collectionName = "\\{$entity}\\Collection";

            $pks = [];
            // we don't want to look up duplicates, just unique values
            foreach ($this->models as $model) {
                $pks[$model->{$fieldGetterFunctionName}()] = $model->{$fieldGetterFunctionName}();
            }
            $infos  = Dao::dao($entity)->records($pks);
            $models = [];

            foreach ($this->models as $key => $model) {
                $k = $model->{$fieldGetterFunctionName}();
                if (isset($infos[$k])) {
                    $model->_preloadCache(
                        $varKey,
                        $models[$key] = $modelName::fromArray($infos[$k])
                    );
                }
            }

            return $collectionName::fromModels($models);
        }

        /**
         * Preload record/link counts based on fields
         *
         * @param string $varKey
         * @param string $entity
         * @param string $foreign_field_name field name used to filter the dao count
         *
         * @return Collection
         * @deprecated
         */
        protected function _preload_counts($varKey, $entity, $foreign_field_name) {

            $pk = static::getPrimaryKeyName();

            // Build list of keyvals to filter the counts
            $fieldvals = [];
            foreach ($this->models as $k => $model) {
                $fieldvals[$k] = [ $foreign_field_name => $model->get($pk), ];
            }

            // Get the counts (as a multi get)
            $counts = Dao::dao($entity)->countMulti($fieldvals);

            // Insert the counts into each model's $_var cache
            foreach ($this->models as $k => $model) {
                $model->_preloadCache(
                    $model::_countVarKey(
                        $varKey,
                        $fieldvals[$k]
                    ),
                    $counts[$k]
                );
            }

            // Return the counts
            return $counts;
        }

        /**
         * Preload record/link counts based on fields
         *
         * @param string $varKey
         * @param string $entity
         * @param string $foreign_field_name field name used to filter the dao count
         *
         * @return Collection
         */
        protected function _preloadCounts($varKey, $entity, $foreignFieldName) {

            $pk = static::getPrimaryKeyName();

            // Build list of keyvals to filter the counts
            $fieldVals = [];
            foreach ($this->models as $k => $model) {
                $fieldVals[$k] = [ $foreignFieldName => $model->get($pk), ];
            }

            // Get the counts (as a multi get)
            $counts = Dao::dao($entity)->countMulti($fieldVals);

            // Insert the counts into each model's $_var cache
            foreach ($this->models as $k => $model) {
                $model->_preloadCache(
                    $model::_countVarKey(
                        $varKey,
                        $fieldVals[$k]
                    ),
                    $counts[$k]
                );
            }

            // Return the counts
            return $counts;
        }
    }


