<?php

    namespace Neoform\Entity\Repo\Cache\Driver;

    use Neoform;

    class Redis implements Neoform\Entity\Repo\Cache\Driver {

        /**
         * @var Neoform\Service\Service
         */
        protected $redisService;

        /**
         * @param Neoform\Redis $redisService
         */
        public function __construct(Neoform\Redis $redisService) {
            $this->redisService = $redisService;
        }

        /**
         * Activate a pipelined (batch) query
         *
         * @return $this
         * @throws Neoform\Entity\Repo\Exception
         */
        public function batchStart() {
            $redis = $this->redisService->get();
            
            if ($redis->isBatchActive()) {
                throw new Neoform\Entity\Repo\Exception('Batch operation already active');
            }

            $redis->multi();
            return $this;
        }

        /**
         * Execute pipelined (batch) queries and return result
         *
         * @return array result of batch operation
         * @throws Neoform\Entity\Repo\Exception
         */
        public function batchExecute() {
            $redis = $this->redisService->get();

            if (! $redis->isBatchActive()) {
                throw new Neoform\Entity\Repo\Exception('No batch operation active');
            }

            return $redis->exec();
        }

        /**
         * Checks if cached record exists.
         *
         * @param string $key
         *
         * @return bool
         */
        public function exists($key) {
            return (bool) $this->redisService->get()->exists($key);
        }

        /**
         * Gets cached data.
         *  if record does exist, an array with a single element, containing the data.
         *  returns null if record does not exist
         *
         * @param string $key
         *
         * @return array|null - if record exists it's wrapped in an array
         * @throws Neoform\Entity\Repo\Exception
         */
        public function get($key) {

            $redis = $this->redisService->get();

            if ($redis->isBatchActive()) {
                throw new Neoform\Entity\Repo\Exception('Batch operation in progress, cannot execute get() at the same time');
            }

            // Batch execute since phpredis returns false if the key doesn't exist on a GET command, which might actually
            // be the stored value... which is not helpful.
            $result = $redis
                ->multi()
                ->exists($key)
                ->get($key)
                ->exec();

            return $result[0] === true ? [ $result[1], ] : null;
        }

        /**
         * @param string   $key
         * @param mixed    $data
         * @param int|null $ttl
         *
         * @return int|null number of records set
         */
        public function set($key, $data, $ttl=null) {
            $redis = $this->redisService->get();

            if ($redis->isBatchActive()) {
                if ($ttl === null) {
                    $redis->set($key, $data);
                    return null;
                }

                $redis->setex($key, $data, $ttl);
                return null;
            }

            if ($ttl === null) {
                return (int) $redis->set($key, $data);
            }

            return (int) $redis->setex($key, $data, $ttl);
        }

        /**
         * Fetch multiple rows from redis
         *
         * @param array  $keys
         *
         * @return array
         * @throws Neoform\Entity\Repo\Exception
         */
        public function getMulti(array $keys) {
            $redis = $this->redisService->get();

            if ($redis->isBatchActive()) {
                throw new Neoform\Entity\Repo\Exception('Batch operation in progress, cannot execute getMulti() at the same time');
            }

            $redis->multi();

            // Redis returns the results in order - if the key doesn't exist, false is returned - this problematic
            // since false might be an actual value being stored... therefore we check if the key exists if false is
            // returned

            foreach ($keys as $key) {
                $redis->exists($key);
                $redis->get($key);
            }

            $results      = [];
            $redisResults = $redis->exec();
            $i            = 0;
            foreach ($keys as $k => $key) {
                if ($redisResults[$i]) {
                    $results[$k] = $redisResults[$i + 1];
                }

                $i += 2;
            }

            return $results;
        }

        /**
         * Set multiple records at the same time
         *
         * It is recommended that this function be wrapped in pipeline_start() and pipeline_execute();
         *
         * @param array    $rows
         * @param int|null $ttl
         *
         * @return int|null number of records set
         */
        public function setMulti(array $rows, $ttl=null) {

            if (! $rows) {
                return 0;
            }

            $redis = $this->redisService->get();

            if ($ttl) {

                if ($redis->isBatchActive()) {
                    foreach ($rows as $k => $v) {
                        $redis->set($k, $v, $ttl);
                    }
                    return null;
                }

                $redis->multi();
                foreach ($rows as $k => $v) {
                    $redis->set($k, $v, $ttl);
                }
                return (int) count(array_filter($redis->exec()));
            }

            // No need to check if it's a batch, since the result will be null
            if ($redis->mset($rows)) {
                return count($rows);
            }
        }

        /**
         * Delete a single record
         *
         * @param string $key
         *
         * @return int|null the number of keys deleted
         */
        public function delete($key) {
            $redis = $this->redisService->get();

            if ($redis->isBatchActive()) {
                $redis->delete($key);
                return null;
            }

            return (int) $redis->delete($key);
        }

        /**
         * Delete multiple entries from cache
         *
         * @param array $keys
         *
         * @return int|null the number of keys deleted
         */
        public function deleteMulti(array $keys) {
            if (! $keys) {
                return 0;
            }

            $redis = $this->redisService->get();

            if ($redis->isBatchActive()) {
                $redis->delete($keys);
            }

            return (int) $redis->delete($keys);
        }

        /**
         * Expire a single record
         *
         * @param string  $key
         * @param integer $ttl how many seconds left for this key to live - if not set, it will expire now
         *
         * @return int|null the number of keys deleted
         */
        public function expire($key, $ttl=0) {
            $redis = $this->redisService->get();

            if ($redis->isBatchActive()) {
                if ($ttl) {
                    $redis->expire($key, $ttl);
                    return null;
                }

                $redis->delete($key);
                return null;
            }

            if ($ttl) {
                return (int) $redis->expire($key, $ttl);
            }

            return (int) $redis->delete($key);
        }

        /**
         * Expire multiple entries
         *
         * @param array   $keys
         * @param integer $ttl how many seconds left for this key to live - if not set, it will expire now
         *
         * @return int|null the number of keys deleted
         */
        public function expireMulti(array $keys, $ttl=0) {
            $redis = $this->redisService->get();

            if ($ttl) {

                if ($redis->isBatchActive()) {

                    foreach ($keys as $key) {
                        $redis->expire($key, $ttl);
                    }
                    return null;
                }

                $redis->multi();
                foreach ($keys as $key) {
                    $redis->expire($key, $ttl);
                }
                return (int) array_sum($redis->exec());
            }

            if ($redis->isBatchActive()) {
                $redis->delete($keys);
            }

            return (int) $redis->delete($keys);
        }

        /**
         * @return bool
         */
        public function flush() {
            return (bool) $this->redisService->get()->flushDB();
        }
    }