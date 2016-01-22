<?php

    namespace Neoform\Entity\Link;

    use Neoform\Entity\Exception;
    use Neoform;

    /**
     * Link Collection
     */
    abstract class Collection
        extends Neoform\Entity\Collection
        implements Neoform\Entity\Link\Entity {

        /**
         * @var string
         */
        protected $type;

        /**
         * @param array|null  $arrs
         * @param string|null $mapField
         */
        public static function fromArrays(array $arrs=null, $mapField=null) {

            $collection = new static;

            if (count($arrs)) {
                $modelClassName = '\\' . static::getNamespace() . '\\Model';
                $current = current($arrs);
                if (is_array($current)) {
                    $collection->type = 'model';
                } else if ($current instanceof $modelClassName) {
                    $collection->type = 'object';
                } else {
                    $collection->type = 'value';
                }

                foreach ($arrs as $key => $arr) {
                    if ($collection->type === 'model') {
                        try {
                            $model = $modelClassName::fromArray($arr);
                        } catch (Exception $e) {
                        }
                    } else if ($collection->type === 'object') {
                        $model = $arr;
                    } else {
                        $model = $arr === null ? null : (int) $arr;
                    }

                    if ($mapField !== null) {
                        $collection->models[$arr[$mapField]] = $model;
                    } else {
                        $collection->models[(int) $key] = $model;
                    }
                }
            }
        }

        /**
         * @param array       $info
         * @param string|null $mapField
         *
         * @return Collection
         */
        public function add(array $info, $mapField=null) {
            if ($this->type === 'model') {
                $modelClassName = '\\' . static::getNamespace() . '\\Model';
                $v = $modelClassName::fromArray($info);
            } else if ($this->type === 'object') {
                $v = $info;
            } else {
                $v = $info === null ? null : $info;
            }

            if ($mapField !== null) {
                $this->models[$info[$mapField]] = $v;
            } else {
                $this->models[] = $v;
            }

            //reset
            $this->_vars = [];

            return $this;
        }

        /**
         * Remap the collection according to a certain field - this makes the key of the collection be that field
         *
         * @param string $field
         *
         * @return Collection
         * @throws exception
         */
        public function remap($field) {
            if ($this->type !== 'model' || $this->type !== 'object') {
                throw new Exception('You cannot remap a value based collection');
            }

            $new = [];
            foreach ($this->models as $model) {
                $new[$model->get($field)] = $model;
            }
            $this->models = $new;

            return $this;
        }

        /**
         * Get an array containing the values of all rows, populated by the specified field
         *
         * @param string $field
         *
         * @return array
         * @throws exception
         */
        public function field($field) {
            if ($this->type !== 'model' || $this->type !== 'object') {
                throw new Exception('You cannot get a field from a value based collection');
            }

            if (! array_key_exists($field, $this->_vars)) {
                $this->_vars[$field] = [];
                foreach ($this->models as $model) {
                    $this->_vars[$field][] = $model->get($field);
                }
            }
            return $this->_vars[$field];
        }
    }