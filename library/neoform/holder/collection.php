<?php

    namespace neoform;

    use ArrayObject;

    class holder_collection extends ArrayObject {

        protected $_vars = []; //caching

        public function __construct(array $infos=null, $map_field=null) {

            if (\count($infos)) {
                $model = 'neoform\\' . static::MODEL;
                foreach ($infos as $key => $info) {
                    try {
                        if (\is_array($info)) {
                            if ($map_field !== null) {
                                $this[$info[$map_field]] = new $model($info);
                            } else {
                                $this[(int) $key] = new $model($info);
                            }
                        }
                    } catch (entity_exception $e) {

                    }
                }
            }
        }

        public function add(array $info, $map_field=null) {
            if (\is_array($info)) {
                $model = 'neoform\\' . static::MODEL;
                $v = new $model($info);
            } else {
                $v = $info === null ? null : (int) $info;
            }

            if ($map_field !== null) {
                $this[$info[$map_field]] = $v;
            } else {
                $this[] = $v;
            }

            //reset
            $this->_vars = [];
        }

        public function del($k) {
            unset($this[$k]);

            //reset
            $this->_vars = [];
        }

        //remap the collection according to a certain field - this makes the key of the collection be that field
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

        public function field($field) {
            if (! \array_key_exists($field, $this->_vars)) {
                $this->_vars[$field] = [];
                foreach ($this as $record) {
                    $this->_vars[$field][] = $record->$field;
                }
            }
            return $this->_vars[$field];
        }

        //exports an array with all the data, or select fields
        public function export($fields=null) {
            $return = [];
            foreach ($this as $k => $v) {
                $return[$k] = $v->export($fields);
            }
            return $return;
        }

        public function sort($f, $order='asc') {
            if (\is_callable($f)) {
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
        }
    }