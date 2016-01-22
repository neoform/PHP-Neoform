<?php

    namespace Neoform\Entity\Holder;

    use Neoform\Entity\Exception;
    use Neoform;

    class Collection extends Neoform\Entity\Collection  {

        protected $_vars = [];

        /**
         * @param array  $infos
         * @param string $map_field
         */
        public function __construct(array $infos=null, $map_field=null) {

            if ($infos) {
                $model = '\\' . static::getNamespace() . '\\Model';
                foreach ($infos as $key => $info) {
                    try {
                        if (is_array($info)) {
                            if ($map_field !== null) {
                                $this[$info[$map_field]] = new $model($info);
                            } else {
                                $this[(int) $key] = new $model($info);
                            }
                        }
                    } catch (Exception $e) {

                    }
                }
            }
        }

        /**
         * Add a model to this collection
         *
         * @param array $info
         * @param null  $map_field
         */
        public function add(array $info, $map_field=null) {
            $model = '\\' . static::getNamespace() . '\\Model';
            $v = new $model($info);

            if ($map_field !== null) {
                $this[$info[$map_field]] = $v;
            } else {
                $this[] = $v;
            }

            //reset
            $this->_vars = [];
        }

        /**
         * @param string $k
         */
        public function del($k) {
            unset($this[$k]);

            //reset
            $this->_vars = [];
        }

        /**
         * Remap the collection according to a certain field - this makes the key of the collection be that field
         *
         * @param string $field
         * @param bool   $ignore_null
         *
         * @return $this
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
         * Exports an array with all the data, or select fields
         *
         * @param array|null $fields
         *
         * @return array
         */
        public function export(array $fields=null) {
            $return = [];
            foreach ($this as $k => $model) {
                $return[$k] = $model->export($fields);
            }
            return $return;
        }

        /**
         * @param string|callable $f
         * @param string          $order
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
        }
    }