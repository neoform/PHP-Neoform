<?php

    namespace Neoform\Entity\Repo\MetaCache\Driver;

    use Neoform;

    class Redis implements Neoform\Entity\Repo\MetaCache\Driver {

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
         * Append a value to multiple lists
         *
         * @param string[] $listKeys
         * @param string   $cacheKey to be put in the lists
         *
         * @return int|null number of elements added
         * @throws Neoform\Entity\Repo\Exception
         */
        public function listAppend(array $listKeys, $cacheKey) {
            $redis = $this->redisService->get();
            if (! $redis->isBatchActive()) {
                $redis->multi();
                foreach ($listKeys as $listKey) {
                    $redis->sAdd($listKey, $cacheKey);
                }
                return (int) count(array_filter($redis->exec()));
            }

            foreach ($listKeys as $listKey) {
                $redis->sAdd($listKey, $cacheKey);
            }
        }

        /**
         * Append values to a list
         *
         * @param string[][] $cacheKeys
         *
         * @return int|null number of elements added
         * @throws Neoform\Entity\Repo\Exception
         */
        public function listAppendMulti(array $cacheKeys) {
            $redis = $this->redisService->get();

            if (! $redis->isBatchActive()) {

                $redis->multi();
                foreach ($cacheKeys as $cacheKey => $listKeys) {
                    foreach ($listKeys as $listKey) {
                        $redis->sAdd($listKey, $cacheKey);
                    }
                }

                return (int) count(array_filter($redis->exec()));
            }

            foreach ($cacheKeys as $cacheKey => $listKeys) {
                foreach ($listKeys as $listKey) {
                    $redis->sAdd($listKey, $cacheKey);
                }
            }
        }

        /**
         * Removes an item from a list
         *
         * @param string $listKey
         * @param string $cacheKey
         *
         * @return int|null
         */
        public function listRemove($listKey, $cacheKey) {
            $redis = $this->redisService->get();

            if (! $redis->isBatchActive()) {
                return (int) $redis->sRem($listKey, $cacheKey);
            }

            $redis->sRem($listKey, $cacheKey);
        }

        /**
         * Removes an item from multiple lists
         *
         * @param string   $cacheKey
         * @param string[] $listKeys
         *
         * @return int|null
         */
        public function listRemoveMulti($cacheKey, array $listKeys) {
            $redis = $this->redisService->get();

            if (! $redis->isBatchActive()) {
                $redis->multi();
                foreach ($listKeys as $listKey) {
                    $redis->sRem($listKey, $cacheKey);
                }
                return (int) count(array_filter($redis->exec()));
            }

            foreach ($listKeys as $listKey) {
                $redis->sRem($listKey, $cacheKey);
            }
        }

        /**
         * Merge multiple lists and fetch results
         *
         * @param string[] $listKeys
         *
         * @return array|null
         */
        public function listUnion(array $listKeys) {

            $redis = $this->redisService->get();

            if (! $redis->isBatchActive()) {
                return array_unique($redis->sUnion($listKeys));
            }

            $redis->sUnion($listKeys);
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
            $redis = $this->redisService->get();

            if ($redis->isBatchActive()) {
                throw new Neoform\Entity\Repo\Exception('Batch operation in progress, cannot execute listPull() at the same time');
            }

            return array_unique(
                $redis
                    ->multi()           // Batch execute
                    ->sUnion($listKeys) // Get all cache keys from the meta lists
                    ->delete($listKeys) // Delete those meta lists
                    ->exec()[0]         // Return the result of the union
            );
        }

        /**
         * @return bool
         */
        public function flush() {
            return (bool) $this->redisService->get()->flushDB();
        }
    }