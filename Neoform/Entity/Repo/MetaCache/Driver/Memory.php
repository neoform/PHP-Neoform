<?php

    namespace Neoform\Entity\Repo\MetaCache\Driver;

    use Neoform;

    /**
     * Cache variables in memory
     */
    class Memory implements Neoform\Entity\Repo\MetaCache\Driver {

        /**
         * @var array holds the cache
         */
        private $vals = [];

        /**
         * @var Memory
         */
        protected static $instances = [];

        /**
         * @param string $enginePool
         *
         * @return Memory|static
         */
        public static function getInstance($enginePool) {
            if (! isset(self::$instances[$enginePool])) {
                self::$instances[$enginePool] = new static;
            }

            return self::$instances[$enginePool];
        }

        /**
         * Append a value to multiple lists
         *
         * @param string[] $listKeys
         * @param string   $cacheKey to be put in the lists
         *
         * @return int number of elements added
         * @throws Neoform\Entity\Repo\Exception
         */
        public function listAppend(array $listKeys, $cacheKey) {
            $i = 0;

            foreach ($listKeys as $listKey) {
                if (array_key_exists($listKey, $this->vals)) {
                    if (! is_array($this->vals[$listKey])) {
                        throw new Neoform\Entity\Repo\Exception('Value is not a list');
                    }

                    if (! in_array($cacheKey, $this->vals[$listKey])) {
                        $this->vals[$listKey][] = $cacheKey;
                        $i++;
                    }
                } else {
                    $this->vals[$listKey] = [ $cacheKey, ];
                    $i++;
                }
            }

            return $i;
        }

        /**
         * Append values to a list
         *
         * @param string[][] $cacheKeys
         *
         * @return int number of elements added
         * @throws Neoform\Entity\Repo\Exception
         */
        public function listAppendMulti(array $cacheKeys) {
            $i = 0;

            foreach ($cacheKeys as $cacheKey => $listKeys) {
                foreach ($listKeys as $listKey) {
                    if (array_key_exists($listKey, $this->vals)) {
                        if (! is_array($this->vals[$listKey])) {
                            throw new Neoform\Entity\Repo\Exception('Value is not a list');
                        }

                        if (! in_array($cacheKey, $this->vals[$listKey])) {
                            $this->vals[$listKey][] = $cacheKey;
                            $i++;
                        }
                    } else {
                        $this->vals[$listKey] = [ $cacheKey, ];
                        $i++;
                    }
                }
            }

            return $i;
        }

        /**
         * Removes an item from a list
         *
         * @param string $listKey
         * @param string $cacheKey
         *
         * @return int
         */
        public function listRemove($listKey, $cacheKey) {

            if (! isset($this->vals[$listKey])) {
                return 0;
            }

            $k = array_search($cacheKey, $this->vals[$listKey]);

            if ($k === false) {
                return 0;
            }

            unset($this->vals[$listKey][$k]);
            return 1;
        }

        /**
         * Removes an item from multiple lists
         *
         * @param string   $cacheKey
         * @param string[] $listKeys
         *
         * @return int
         */
        public function listRemoveMulti($cacheKey, array $listKeys) {

            $i = 0;
            foreach ($listKeys as $listKey) {
                $k = array_search($cacheKey, $this->vals[$listKey]);

                if ($k !== false) {
                    $i++;
                    unset($this->vals[$listKey][$k]);
                }
            }

            return $i;
        }

        /**
         * Merge multiple lists and fetch results
         *
         * @param string[] $listKeys
         *
         * @return array
         */
        public function listUnion(array $listKeys) {
            $list = [];
            $arrs = array_intersect_key(
                $this->vals,
                array_flip($listKeys)
            );

            foreach ($arrs as $arr) {
                foreach ($arr as $val) {
                    $list[] = $val;
                }
            }

            return array_unique($list);
        }

        /**
         * Get a union of multiple lists
         *
         * @param string[] $listKeys
         *
         * @return array
         * @throws Neoform\Entity\Repo\Exception
         */
        public function listPull(array $listKeys) {
            $keys = [];
            foreach ($listKeys as $listKey) {
                if (isset($this->vals[$listKey])) {
                    foreach ($this->vals[$listKey] as $key) {
                        $keys[] = $key;
                    }
                    unset($this->vals[$listKey]);
                }
            }
            return array_unique($keys);
        }

        /**
         * Delete all values
         *
         * @return bool
         */
        public function flush() {
            $this->vals = [];
            return true;
        }
    }