<?php

    /**
     * Record Collection
     */
    class entity_record_collection extends ArrayObject {

        /**
         * @var array locally cached data
         */
        protected $_vars = []; //caching

        /**
         * Construct
         *
         * @param array              $pks       Primary keys
         * @param array|entity_record_model $infos     Preloaded data array
         * @param string|null        $map_field Assign collection keys based on this field (taken from the models)
         */
        public function __construct(array $pks=null, array $infos=null, $map_field=null) {

            if ($pks !== null) {
                $infos = entity::dao(static::ENTITY_NAME)->records($pks);
            }

            if ($infos !== null && $infos) {
                $model = static::ENTITY_NAME . '_model';
                foreach ($infos as $key => $info) {
                    try {
                        if (is_array($info)) {
                            $this[$map_field !== null ? $info[$map_field] : $key] = new $model(null, $info);
                        } else if ($info instanceof $model) {
                            $this[$map_field !== null ? $info->$map_field : $key] = $info;
                        }
                    } catch (entity_exception $e) {

                    }
                }
            }
        }

        /**
         * Get a collection by a given field or fields
         * folder_collection::by_parent(5) will return a folder collection.
         * this is just a shortcut for new folder_collection(entity::dao('folder')->by_parent(5));
         *
         * @param string $name
         * @param array $args
         *
         * @return entity_record_collection
         */
        public static function __callstatic($name, array $args) {
            $collection = static::ENTITY_NAME . '_collection';
            if ($name === 'all' || $name === 'records') {
                return new $collection(null, call_user_func_array([entity::dao(static::ENTITY_NAME), $name], $args));
            } else {
                return new $collection(call_user_func_array([entity::dao(static::ENTITY_NAME), $name], $args));
            }
        }

        /**
         * Add a model to the collection
         *
         * @param array|entity_record_model $info
         * @param string|null        $map_field Assign collection keys based on this field (taken from the models)
         *
         * @return entity_record_collection $this
         */
        public function add($info, $map_field=null) {
            $model = static::ENTITY_NAME . '_model';
            if ($info instanceof $model) {
                $v = $info;
            } else if (is_array($info)) {
                $v = new $model(null, $info);
            } else {
                $v = new $model($info);
            }

            if ($map_field !== null) {
                $this[$v->$map_field] = $v;
            } else {
                $this[] = $v;
            }

            //reset
            $this->_vars = [];

            return $this;
        }

        /**
         * Remove a model from the collection
         *
         * @param mixed $k Key
         *
         * @return entity_record_collection $this
         */
        public function del($k) {
            unset($this[$k]);

            //reset
            $this->_vars = [];

            return $this;
        }

        /**
         * Remap the collection according to a certain field - this makes the key of the collection be that field
         *
         * @param string $field
         * @param bool   $ignore_null
         *
         * @return entity_record_collection $this
         */
        public function remap($field, $ignore_null=false) {
            $new = [];
            if ($ignore_null) {
                foreach ($this as $record) {
                    if ($record->$field !== null) {
                        $new[$record->$field] = $record;
                    }
                }
            } else {
                foreach ($this as $record) {
                    $new[$record->$field] = $record;
                }
            }
            $this->exchangeArray($new);
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
            if (! array_key_exists($field, $this->_vars)) {
                $arr = [];
                foreach ($this as $k => $record) {
                    $arr[$key ? $record->$key : $k] = $record->$field;
                }
                $this->_vars[$field] = $arr;
            }
            return $this->_vars[$field];
        }

        /**
         * Exports an array with all the data from the models, or select fields
         *
         * @param array|null $fields to export
         *
         * @return array
         */
        public function export(array $fields=null) {
            $return = [];
            foreach ($this as $k => $v) {
                $return[$k] = $v->export($fields);
            }
            return $return;
        }

        /**
         * Sort the collection based on $f (function) or $f field name
         *
         * @param callable|string $f
         * @param string          $order
         *
         * @return entity_record_collection
         */
        public function sort($f, $order='asc') {
            if (is_callable($f)) {
                $this->uasort(function ($a, $b) use ($f, $order) {
                    $a = $f($a);
                    $b = $f($b);

                    if ($a === $b) {
                        return 0;
                    }

                    if ($order === 'asc') {
                        return ($a < $b) ? -1 : 1;
                    } else {
                        return ($a > $b) ? -1 : 1;
                    }
                });
            } else {
                $this->uasort(function ($a, $b) use ($f, $order) {
                    $a_field = $a->$f;
                    $b_field = $b->$f;

                    if ($a_field === $b_field) {
                        return 0;
                    }

                    if ($order === 'asc') {
                        return ($a_field < $b_field) ? -1 : 1;
                    } else {
                        return ($a_field > $b_field) ? -1 : 1;
                    }
                });
            }
            return $this;
        }

        /**
         * Get many groups of records all in one shot. This greatly reduces the number of requests to the cache service,
         * which can greatly speed up an application.
         *
         * @param string       $entity         eg, user
         * @param string       $by_function    eg, by_comments
         * @param string       $model_var_name Key (in $model::$_var) that stores preloaded model data
         * @param array|null   $order_by       array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset         get PKs starting at this offset
         * @param integer|null $limit          max number of PKs to return
         *
         * @return entity_record_collection
         */
        protected function _preload_one_to_many($entity, $by_function, $model_var_name, array $order_by=null,
                                                $offset=null, $limit=null) {

            $collection_name  = "{$entity}_collection";
            $model_name       = "{$entity}_model";
            $dao              = entity::dao($entity);
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
            $models = new $collection_name(null, $dao->records($pks));

            // sort flat array back into grouped data again
            foreach ($pks_groups as & $pks_group) {
                foreach ($pks_group as $k => $pk) {
                    if (isset($models[$pk])) {
                        $pks_group[$k] = $models[$pk];
                    } else {
                        // this shouldn't actually happen... if it did, something went wrong in the DAO
                        unset($pks_group[$k]);
                    }
                }
            }

            // load the child models into the $_var of each model in this collection
            foreach ($this as $key => $model) {
                if (isset($pks_groups[$key])) {
                    $model->_set_var(
                        $model_name::_limit_var_key(
                            $model_var_name,
                            $order_by,
                            $offset,
                            $limit
                        ),
                        new $collection_name(null, $pks_groups[$key])
                    );
                }
            }
            return $models;
        }

        /**
         * Get many groups of records all in one shot. This greatly reduces the number of requests to the cache service,
         * which can greatly speed up an application.
         *
         * @param string       $entity         eg, user_permission
         * @param string       $by_function    eg, by_user
         * @param string       $foreign_type   eg, permission
         * @param string       $model_var_name Key (in $model::$_var) that stores preloaded model data
         * @param array|null   $order_by       array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset         get PKs starting at this offset
         * @param integer|null $limit          max number of PKs to return
         *
         * @return entity_record_collection
         */
        protected function _preload_many_to_many($entity, $by_function, $foreign_type, $model_var_name,
                                                 array $order_by=null, $offset=null, $limit=null) {

            $by_function             .= '_multi';
            $foreign_collection_name  = "{$foreign_type}_collection";

            // Get the ids for those
            $pks_groups = entity::dao($entity)->$by_function($this, $order_by, $offset, $limit);
            $pks        = [];

            // make a flat array of all keys, removing dupes along the way.
            foreach ($pks_groups as $pks_group) {
                foreach ($pks_group as $pk) {
                    $pks[$pk] = $pk;
                }
            }

            // get all the records all in one shot
            $models = new $foreign_collection_name(null, entity::dao($foreign_type)->records($pks));

            // sort flat array back into grouped data again
            foreach ($pks_groups as & $pks_group) {
                foreach ($pks_group as $k => $pk) {
                    if (isset($models[$pk])) {
                        $pks_group[$k] = $models[$pk];
                    } else {
                        // this shouldn't actually happen... if it did, something went wrong in the DAO
                        unset($pks_group[$k]);
                    }
                }
            }

            foreach ($this as $key => $model) {
                if (isset($pks_groups[$key])) {
                    $model->_set_var(
                        $model_var_name,
                        new $foreign_collection_name(null, $pks_groups[$key])
                    );
                }
            }
            return $models;
        }

        /**
         * Get many groups of records all in one shot. This greatly reduces the number of requests to the cache service,
         * which can greatly speed up an application.
         *
         * @param string $entity         Name of the entity
         * @param string $field          Corresponding field for that entity
         * @param string $model_var_name Key (in $model::$_var) that stores preloaded model data
         *
         * @return entity_record_collection
         */
        protected function _preload_one_to_one($entity, $field, $model_var_name) {

            $model_name      = "{$entity}_model";
            $collection_name = "{$entity}_collection";

            $pks = [];
            // we don't want to look up duplicates, just unique values
            foreach ($this as $model) {
                $pks[$model->$field] = $model->$field;
            }
            $infos  = entity::dao($entity)->records($pks);
            $models = [];

            foreach ($this as $key => $model) {
                $k = $model->$field;
                if (isset($infos[$k])) {
                    $model->_set_var(
                        $model_var_name,
                        $models[$key] = new $model_name(null, $infos[$k])
                    );
                }
            }

            return new $collection_name(null, $models);
        }

        /**
         * Preload record/link counts based on fields
         *
         * @param string $entity
         * @param array  $fields list of fields (from the model) to use to determine the count
         * @param string $model_var_name
         *
         * @return entity_record_collection
         */
        protected function _preload_counts($entity, array $fields, $model_var_name) {

            $fieldvals = $this->export($fields);

            $counts = entity::dao($entity)->count_multi($fieldvals);

            foreach ($this as $k => $model) {
                $model->_set_var(
                    $model::_count_var_key(
                        $model_var_name,
                        $fieldvals[$k]
                    ),
                    isset($counts[$k]) ? $counts[$k] : 0
                );
            }
            return $counts;
        }
    }


