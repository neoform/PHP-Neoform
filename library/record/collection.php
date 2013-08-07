<?php

    /**
     * Record Collection
     */
    class record_collection extends ArrayObject {

        /**
         * @var array locally cached data
         */
        protected $_vars = []; //caching

        /**
         * Construct
         *
         * @param array              $pks       Primary keys
         * @param array|record_model $infos     Preloaded data array
         * @param string|null        $map_field Assign collection keys based on this field (taken from the models)
         */
        public function __construct(array $pks=null, array $infos=null, $map_field=null) {

            if ($pks !== null) {
                $infos = entity_dao::get(static::ENTITY_NAME)->by_pks($pks);
            }

            if ($infos !== null && count($infos)) {
                $model = static::ENTITY_NAME . '_model';
                foreach ($infos as $key => $info) {
                    try {
                        if (is_array($info)) {
                            $this[$map_field !== null ? $info[$map_field] : $key] = new $model(null, $info);
                        } else if ($info instanceof $model) {
                            $this[$map_field !== null ? $info->$map_field : $key] = $info;
                        }
                    } catch (model_exception $e) {

                    }
                }
            }
        }

        /**
         * Get a collection by a given field or fields
         * folder_collection::by_parent(5) will return a folder model.
         * this is just a shortcut for new folder_model(entity_dao::get('folder')->by_parent(5));
         *
         * @param string $name
         * @param array $args
         *
         * @return record_collection
         */
        public static function __callstatic($name, array $args) {
            $collection = static::ENTITY_NAME . '_collection';
            if ($name === 'by_all') {
                return new $collection(null, call_user_func_array([entity_dao::get(static::ENTITY_NAME), $name], $args));
            } else {
                return new $collection(call_user_func_array([entity_dao::get(static::ENTITY_NAME), $name], $args));
            }
        }

        /**
         * Add a model to the collection
         *
         * @param array|record_model $info
         * @param string|null        $map_field Assign collection keys based on this field (taken from the models)
         *
         * @return record_collection $this
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
         * @param $k Key
         *
         * @return record_collection $this
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
         * @return record_collection $this
         */
        public function remap($field, $ignore_null=false) {
            $new = [];
            foreach ($this as $record) {
                if (! $ignore_null || $record->$field !== null) {
                    $new[$record->$field] = $record;
                }
            }
            $this->exchangeArray($new);
            return $this;
        }

        /**
         * Get an array with the values of the models in the collection
         *
         * @param string $field
         *
         * @return array
         */
        public function field($field) {
            if (! array_key_exists($field, $this->_vars)) {
                $this->_vars[$field] = [];
                foreach ($this as $k => $record) {
                    $this->_vars[$field][$k] = $record->$field;
                }
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
         * @return record_collection
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
         * Get many groups of records all in one shot.
         * this is ideal for installations where memcached
         * is not on the same machine as this php app,
         * since it reduces the number of hits to memcached
         * to two multigets to populate all child collections
         *
         * @param string      $entity          eg, user
         * @param string      $by_function     eg, by_comments
         * @param string|null $method_override Override the name of the model function being preloaded
         *
         * @return record_collection
         */
        protected function _preload_one_to_many($entity, $by_function, $method_override=null) {

            $collection_name  = "{$entity}_collection";
            $dao              = entity_dao::get($entity);
            $by_function     .= '_multi';

            // Get the ids for those
            $pks_groups = $dao->$by_function($this);
            $pks        = [];

            // make a flat array of all keys, removing dupes along the way.
            foreach ($pks_groups as $pks_group) {
                foreach ($pks_group as $pk) {
                    $pks[$pk] = $pk;
                }
            }

            // get all the records all in one shot
            $models = new $collection_name(null, $dao->by_pks($pks));

            // sort flat array back into grouped data again
            foreach ($pks_groups as & $pks_group) {
                foreach ($pks_group as $k => $pk) {
                    if (isset($models[$pk])) {
                        $pks_group[$k] = $models[$pk];
                    } else {
                        // this shouldn't actually happen...
                        unset($pks_group[$k]);
                    }
                }
            }

            foreach ($this as $key => $model) {
                if (isset($pks_groups[$key])) {
                    $model->_set_var(
                        $method_override !== null ? $method_override : $collection_name,
                        new $collection_name(null, $pks_groups[$key])
                    );
                }
            }
            return $models;
        }

        /**
         * Get many groups of records all in one shot.
         * this is ideal for installations where memcached
         * is not on the same machine as this php app,
         * since it reduces the number of hits to memcached
         * to two multigets to populate all child collections
         *
         * @param string      $entity          eg, user_permission
         * @param string      $by_function     eg, by_user
         * @param string      $foreign_type    eg, permission
         * @param string|null $method_override Override the name of the model function being preloaded
         *
         * @return record_collection
         */
        protected function _preload_many_to_many($entity, $by_function, $foreign_type, $method_override=null) {

            $by_function        .= '_multi';
            $foreign_collection  = "{$foreign_type}_collection";

            // Get the ids for those
            $pks_groups = entity_dao::get($entity)->$by_function($this);
            $pks        = [];

            // make a flat array of all keys, removing dupes along the way.
            foreach ($pks_groups as $pks_group) {
                foreach ($pks_group as $pk) {
                    $pks[$pk] = $pk;
                }
            }

            // get all the records all in one shot
            $models = new $foreign_collection(null, entity_dao::get($foreign_type)->by_pks($pks));

            // sort flat array back into grouped data again
            foreach ($pks_groups as & $pks_group) {
                foreach ($pks_group as $k => $pk) {
                    if (isset($models[$pk])) {
                        $pks_group[$k] = $models[$pk];
                    } else {
                        // this shouldn't actually happen...
                        unset($pks_group[$k]);
                    }
                }
            }

            foreach ($this as $key => $model) {
                if (isset($pks_groups[$key])) {
                    $model->_set_var(
                        $method_override !== null ? $method_override : $foreign_collection,
                        new $foreign_collection(null, $pks_groups[$key])
                    );
                }
            }
            return $models;
        }

        /**
         * Get many groups of records all in one shot.
         * this is ideal for installations where memcached
         * is not on the same machine as this php app,
         * since it reduces the number of hits to memcached
         * to two multigets to populate all child collections
         *
         * @param string      $entity          Name of the entity
         * @param string      $field           Corresponding field for that entity
         * @param string|null $method_override Override the name of the model function being preloaded
         *
         * @return record_collection
         */
        protected function _preload_one_to_one($entity, $field, $method_override=null) {

            $dao             = entity_dao::get($entity);
            $model_name      = "{$entity}_model";
            $collection_name = "{$entity}_collection";

            $pks = [];
            // we don't want to look up duplicates, just unique values
            foreach ($this as $model) {
                $pks[$model->$field] = $model->$field;
            }
            $infos  = $dao->by_pks($pks);
            $models = [];

            foreach ($this as $key => $model) {
                $k = $model->$field;
                if (isset($infos[$k])) {
                    $model->_set_var(
                        $method_override ?: $entity,
                        $models[$key] = new $model_name(null, $infos[$k])
                    );
                }
            }

            return new $collection_name(null, $models);
        }
    }


