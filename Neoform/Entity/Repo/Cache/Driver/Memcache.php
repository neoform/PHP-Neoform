<?php

    namespace Neoform\Entity\Repo\Cache\Driver;

    use Neoform;

    class Memcache implements Neoform\Entity\Repo\Cache\Driver {

        /**
         * @var Neoform\Memcache
         */
        protected $memcacheService;

        /**
         * @param Neoform\Memcache $memcacheService
         */
        public function __construct(Neoform\Memcache $memcacheService) {
            $this->memcacheService = $memcacheService;
        }

        /**
         * Activate a pipelined (batch) query - this doesn't exist in memcache, so ignore
         *
         * @return $this
         */
        public function batchStart() {
            return $this;
        }

        /**
         * Execute pipelined (batch) queries and return result - this doesn't exist in memcache, so ignore
         *
         * @return array
         */
        public function batchExecute() {
            return [];
        }

        /**
         * Checks if cached record exists.
         *
         * @param string $key
         *
         * @return bool
         */
        public function exists($key) {
            $memcache = $this->memcacheService->get();
            $memcache->get($key);
            return (bool) $memcache->rowFound();
        }

        /**
         * Gets cached data.
         *  if record does exist, an array with a single element, containing the data.
         *  returns null if record does not exist
         *
         * @param string $key
         *
         * @return array|null - if record exists it's wrapped in an array
         */
        public function get($key) {
            $memcache = $this->memcacheService->get();
            $data = $memcache->get($key);

            if ($memcache->rowFound()) {
                return [ $data, ];
            }
        }

        /**
         * @param string       $key
         * @param mixed        $data
         * @param integer|null $ttl
         *
         * @return int number of records set
         */
        public function set($key, $data, $ttl=null) {
            return (int) $this->memcacheService->get()->set(
                $key,
                $data,
                $ttl
            );
        }

        /**
         * Fetch multiple rows from memcached
         *
         * @param array  $keys
         *
         * @return array
         */
        public function getMulti(array $keys) {
            $mcResults = $this->memcacheService->get()->getMulti($keys);
            $results = [];
            foreach ($keys as $k => $key) {
                if (array_key_exists($key, $mcResults)) {
                    $results[$k] = $mcResults[$key];
                }
            }
            return $results;
        }

        /**
         * Set multiple records at the same time
         *
         * @param array        $rows
         * @param integer|null $ttl
         *
         * @return int number of records set
         */
        public function setMulti(array $rows, $ttl=null) {
            if ($this->memcacheService->get()->setMulti($rows, $ttl)) {
                return count($rows);
            }

            return 0;
        }

        /**
         * Delete a single record
         *
         * @param string $key
         *
         * @return int number of records deleted
         */
        public function delete($key) {
            return (int) $this->memcacheService->get()->delete($key);
        }

        /**
         * Delete multiple entries from cache
         *
         * @param string[] $keys
         *
         * @return int number of records deleted
         */
        public function deleteMulti(array $keys) {
            if ($keys) {
                $mc = $this->memcacheService->get();
                $i = 0;

                // maybe use Memcached::deleteMulti() ? (can't get delete count though)

                foreach ($keys as $key) {
                    $i += (int) $mc->delete($key);
                }
                return $i;
            }

            return 0;
        }

        /**
         * Delete a single record
         *
         * @param string  $key
         * @param integer $ttl how many seconds left for this key to live - if not set, it will expire now
         *
         * @return int number of records expired
         */
        public function expire($key, $ttl=0) {
            if ($ttl === 0) {
                return (int) $this->memcacheService->get()->delete($key);
            } else {
                return (int) $this->memcacheService->get()->touch($key, $ttl);
            }
        }

        /**
         * Delete multiple entries from cache
         *
         * @param array   $keys
         * @param integer $ttl how many seconds left for this key to live - if not set, it will expire now
         *
         * @return int number of records expired
         */
        public function expireMulti(array $keys, $ttl=0) {
            if ($keys) {
                $mc = $this->memcacheService->get();
                $i = 0;

                if ($ttl) {
                    foreach ($keys as $key) {
                        $i += $mc->touch($key, $ttl);
                    }
                    return $i;
                }

                foreach ($keys as $key) {
                    $i += $mc->delete($key);
                }

                return $i;
            }

            return 0;
        }

        /**
         * @return bool
         */
        public function flush() {
            return (bool) $this->memcacheService->get()->flush();
        }
    }