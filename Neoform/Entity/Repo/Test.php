<?php

    namespace Neoform\Entity\Repo;

    use Neoform;

    class Test extends Neoform\Test\Model {

        public function init() {

            $repo = new Neoform\Entity\Repo\Cache\Driver\Redis(Neoform\Redis::getService('master'));
            //$repo = Neoform\Entity\Repo\Cache\Driver\Memory::getInstance(null);

            // Test Memory Cache - Single
            $this->repoCacheSingleSimple($repo, $repo);
            $this->repoCacheSingleCacheEmptyResults($repo, $repo);
            $this->repoCacheSingleAfterCache($repo, $repo);

            // Test Memory Cache - Multi
            $this->repoCacheMultiSimple($repo, $repo);
            $this->repoCacheMultiCacheEmptyResults($repo, $repo);
            $this->repoCacheMultiAfterCache($repo, $repo);
        }

        /**
         * Single
         */
        protected function repoCacheSingleSimple(Cache\Driver $readRepo, Cache\Driver $writeRepo) {
            // Setup test
            $readRepo->flush();
            $writeRepo->flush();

            $cache = new Neoform\Entity\Repo\Cache(
                $readRepo,
                $writeRepo
            );

            // Pull data for the first time
            $pulled = false;
            $result = $cache->single(
                'key1',
                function() use (& $pulled) {
                    $pulled = true;
                    return "Hey sexy pants";
                }
            );

            $this->assertTrue($pulled);
            $this->assertTrue($result === "Hey sexy pants");

            // Pull the same data again, this time it should not get from origin, instead from cache
            $pulled = false;
            $result = $cache->single(
                'key1',
                function() use (& $pulled) {
                    $pulled = true;
                    return "Uh oh...";
                }
            );

            $this->assertTrue(! $pulled);
            $this->assertTrue($result === "Hey sexy pants");

            // Check directly that the record exists in the repo
            $this->assertTrue($readRepo->exists('key1'));
            $this->assertTrue($writeRepo->exists('key1'));
        }

        protected function repoCacheSingleCacheEmptyResults(Cache\Driver $readRepo, Cache\Driver $writeRepo) {
            // Setup test
            $readRepo->flush();
            $writeRepo->flush();

            $cache = new Neoform\Entity\Repo\Cache(
                $readRepo,
                $writeRepo
            );

            // Pull data for the first time
            $pulled = false;
            $result = $cache->single(
                'key1',
                function() use (& $pulled) {
                    $pulled = true;
                    return; // source returns nothing
                }
            );

            $this->assertTrue($pulled);
            $this->assertTrue($result === null);

            // Pull the same data again, it should get from origin again since we did not put $cacheEmptyResults = true
            $pulled = false;
            $result = $cache->single(
                'key1',
                function() use (& $pulled) {
                    $pulled = true;
                    return;
                }
            );

            $this->assertTrue($pulled);
            $this->assertTrue($result === null);

            // Try again, this time with $cacheEmptyResults = true
            $pulled = false;
            $result = $cache->single(
                'key1',
                function() use (& $pulled) {
                    $pulled = true;
                    return;
                },
                null,
                true
            );

            $this->assertTrue($pulled);
            $this->assertTrue($result === null);

            // Try again, this time with $cacheEmptyResults = true, we should not hit source this time
            $pulled = false;
            $result = $cache->single(
                'key1',
                function() use (& $pulled) {
                    $pulled = true;
                    return;
                },
                null,
                true
            );

            $this->assertTrue(! $pulled);
            $this->assertTrue($result === null);

            // Check directly that the record exists in the repo
            $this->assertTrue($readRepo->exists('key1'));
            $this->assertTrue($writeRepo->exists('key1'));
        }

        protected function repoCacheSingleAfterCache(Cache\Driver $readRepo, Cache\Driver $writeRepo) {
            // Setup test
            $readRepo->flush();
            $writeRepo->flush();

            $cache = new Neoform\Entity\Repo\Cache(
                $readRepo,
                $writeRepo
            );

            // Pull data for the first time
            $pulled     = false;
            $afterCache = false;
            $cachedKey  = null;
            $cachedData = null;

            $result = $cache->single(
                'key1',
                function() use (&$pulled) {
                    $pulled = true;
                    return 'cool';
                },
                function($key, $data) use (&$afterCache, &$cachedKey, &$cachedData) {
                    $afterCache = true;
                    $cachedKey  = $key;
                    $cachedData = $data;
                }
            );

            $this->assertTrue($pulled);
            $this->assertTrue($afterCache);
            $this->assertTrue($result === 'cool');
            $this->assertTrue($cachedKey === 'key1');
            $this->assertTrue($cachedData === 'cool');

            // Repeat - We should not be executing the after cache function this time
            $pulled     = false;
            $afterCache = false;
            $cachedKey  = null;
            $cachedData = null;

            $result = $cache->single(
                'key1',
                function() use (&$pulled) {
                    $pulled = true;
                    return 'cool';
                },
                function($key, $data) use (&$afterCache) {
                    $afterCache = true;
                }
            );

            $this->assertTrue(! $pulled);
            $this->assertTrue(! $afterCache);
            $this->assertTrue($result === 'cool');

            // Check directly that the record exists in the repo
            $this->assertTrue($readRepo->exists('key1'));
            $this->assertTrue($writeRepo->exists('key1'));
        }


        /**
         * Multi
         */
        protected function repoCacheMultiSimple(Cache\Driver $readRepo, Cache\Driver $writeRepo) {
            // Setup test
            $readRepo->flush();
            $writeRepo->flush();

            $cache = new Neoform\Entity\Repo\Cache(
                $readRepo,
                $writeRepo
            );

            // Pull data for the first time
            $pulled = 0;
            $keyed  = 0;
            $result = $cache->multi(
                [
                    3 => 111,
                    9 => 222,
                ],
                function($id) use (& $keyed) {
                    $keyed++;
                    return "key{$id}";
                },
                function($keys) use (& $pulled) {
                    foreach ($keys as &$v) {
                        $v = "Hey sexy pants";
                        $pulled++;
                    }
                    return $keys;
                }
            );

            $this->assertTrue(count($result) === 2);
            $this->assertTrue($keyed === 2);
            $this->assertTrue($pulled === 2);
            $this->assertTrue(isset($result[3], $result[9]));
            $this->assertTrue($result[3] === "Hey sexy pants");
            $this->assertTrue($result[9] === "Hey sexy pants");

            // Pull data again, this time only 1 should be pulled
            $pulled = 0;
            $keyed  = 0;
            $result = $cache->multi(
                [
                    3 => 111,
                    9 => 222,
                    4 => 333,
                ],
                function($id) use (& $keyed) {
                    $keyed++;
                    return "key{$id}";
                },
                function($keys) use (& $pulled) {
                    foreach ($keys as &$v) {
                        $v = "Lookin' gooood";
                        $pulled++;
                    }
                    return $keys;
                }
            );

            $this->assertTrue(count($result) === 3);
            $this->assertTrue($keyed === 3);
            $this->assertTrue($pulled === 1);
            $this->assertTrue(isset($result[3], $result[9], $result[4]));
            $this->assertTrue($result[3] === "Hey sexy pants");
            $this->assertTrue($result[9] === "Hey sexy pants");
            $this->assertTrue($result[4] === "Lookin' gooood");

            // Check directly that the records exist in the repo
            $this->assertTrue($readRepo->exists('key111'));
            $this->assertTrue($writeRepo->exists('key111'));
            $this->assertTrue($readRepo->exists('key222'));
            $this->assertTrue($writeRepo->exists('key222'));
            $this->assertTrue($readRepo->exists('key333'));
            $this->assertTrue($writeRepo->exists('key333'));
        }

        protected function repoCacheMultiCacheEmptyResults(Cache\Driver $readRepo, Cache\Driver $writeRepo) {
            // Setup test
            $readRepo->flush();
            $writeRepo->flush();

            $cache = new Neoform\Entity\Repo\Cache(
                $readRepo,
                $writeRepo
            );

            // Pull data for the first time
            $pulled = 0;
            $keyed  = 0;
            $result = $cache->multi(
                [
                    3 => 111,
                    9 => 222,
                ],
                function($id) use (& $keyed) {
                    $keyed++;
                    return "key{$id}";
                },
                function($keys) use (& $pulled) {
                    foreach ($keys as &$v) {
                        $v = null; // source returns nothing
                        $pulled++;
                    }
                    return $keys;
                }
            );

            $this->assertTrue(count($result) === 2);
            $this->assertTrue($keyed === 2);
            $this->assertTrue($pulled === 2);
            $this->assertTrue(array_key_exists(3, $result));
            $this->assertTrue(array_key_exists(9, $result));
            $this->assertTrue($result[3] === null);
            $this->assertTrue($result[9] === null);

            // Pull data again - we should be hitting the source again
            $pulled = 0;
            $keyed  = 0;
            $result = $cache->multi(
                [
                    3 => 111,
                    9 => 222,
                ],
                function($id) use (& $keyed) {
                    $keyed++;
                    return "key{$id}";
                },
                function($keys) use (& $pulled) {
                    foreach ($keys as &$v) {
                        $v = null; // source returns nothing
                        $pulled++;
                    }
                    return $keys;
                },
                null,
                true
            );

            $this->assertTrue(count($result) === 2);
            $this->assertTrue($keyed === 2);
            $this->assertTrue($pulled === 2);
            $this->assertTrue(array_key_exists(3, $result));
            $this->assertTrue(array_key_exists(9, $result));
            $this->assertTrue($result[3] === null);
            $this->assertTrue($result[9] === null);

            // Pull data again - we should be hitting the cache this time
            $pulled = 0;
            $keyed  = 0;
            $result = $cache->multi(
                [
                    3 => 111,
                    9 => 222,
                    4 => 333,
                ],
                function($id) use (& $keyed) {
                    $keyed++;
                    return "key{$id}";
                },
                function($keys) use (& $pulled) {
                    foreach ($keys as &$v) {
                        $v = null; // source returns nothing
                        $pulled++;
                    }
                    return $keys;
                }
            );

            $this->assertTrue(count($result) === 3);
            $this->assertTrue($keyed === 3);
            $this->assertTrue($pulled === 1);
            $this->assertTrue(array_key_exists(3, $result));
            $this->assertTrue(array_key_exists(9, $result));
            $this->assertTrue(array_key_exists(4, $result));
            $this->assertTrue($result[3] === null);
            $this->assertTrue($result[9] === null);
            $this->assertTrue($result[4] === null);

            // Check directly that the records exist in the repo
            $this->assertTrue($readRepo->exists('key111'));
            $this->assertTrue($writeRepo->exists('key111'));
            $this->assertTrue($readRepo->exists('key222'));
            $this->assertTrue($writeRepo->exists('key222'));
            $this->assertTrue(! $readRepo->exists('key333'));  // since we didn't activate empty result caching for this record
            $this->assertTrue(! $writeRepo->exists('key333')); // since we didn't activate empty result caching for this record
        }

        protected function repoCacheMultiAfterCache(Cache\Driver $readRepo, Cache\Driver $writeRepo) {
            // Setup test
            $readRepo->flush();
            $writeRepo->flush();

            $cache = new Neoform\Entity\Repo\Cache(
                $readRepo,
                $writeRepo
            );

            // Pull data for the first time
            $keyed           = 0;
            $pulled          = 0;
            $afterCache      = false;
            $cachedKey       = null;
            $cachedFieldVals = null;
            $cachedResults   = null;
            $result = $cache->multi(
                [
                    3 => 111,
                    9 => 222,
                ],
                function($id) use (& $keyed) {
                    $keyed++;
                    return "key{$id}";
                },
                function($keys) use (& $pulled) {
                    foreach ($keys as &$v) {
                        $v = "Hey sexy pants";
                        $pulled++;
                    }
                    return $keys;
                },
                function(array $cacheKeys, array $fieldValsArr, array $pkResultsArr)
                use (&$afterCache, &$cachedKeys, &$cachedFieldVals, &$cachedResults) {
                    $afterCache      = true;
                    $cachedKeys      = $cacheKeys;
                    $cachedFieldVals = $fieldValsArr;
                    $cachedResults   = $pkResultsArr;
                }
            );

            $this->assertTrue(count($result) === 2);
            $this->assertTrue($keyed === 2);
            $this->assertTrue($pulled === 2);
            $this->assertTrue($afterCache);
            $this->assertTrue(isset($result[3], $result[9]));
            $this->assertTrue(count($cachedKeys) === 2);
            $this->assertTrue(count($cachedFieldVals) === 2);
            $this->assertTrue(count($cachedResults) === 2);

            // Pull data again
            $keyed           = 0;
            $pulled          = 0;
            $afterCache      = false;
            $cachedKey       = null;
            $cachedFieldVals = null;
            $cachedResults   = null;
            $result = $cache->multi(
                [
                    3 => 111,
                    9 => 222,
                    4 => 333,
                ],
                function($id) use (& $keyed) {
                    $keyed++;
                    return "key{$id}";
                },
                function($keys) use (& $pulled) {
                    foreach ($keys as &$v) {
                        $v = "Hey sexy pants";
                        $pulled++;
                    }
                    return $keys;
                },
                function(array $cacheKeys, array $fieldValsArr, array $pkResultsArr)
                use (&$afterCache, &$cachedKeys, &$cachedFieldVals, &$cachedResults) {
                    $afterCache      = true;
                    $cachedKeys      = $cacheKeys;
                    $cachedFieldVals = $fieldValsArr;
                    $cachedResults   = $pkResultsArr;
                }
            );

            $this->assertTrue(count($result) === 3);
            $this->assertTrue($keyed === 3);
            $this->assertTrue($pulled === 1);
            $this->assertTrue($afterCache);
            $this->assertTrue(isset($result[3], $result[9], $result[4]));
            $this->assertTrue(count($cachedKeys) === 1);
            $this->assertTrue(count($cachedFieldVals) === 1);
            $this->assertTrue(count($cachedResults) === 1);

            // Check directly that the records exist in the repo
            $this->assertTrue($readRepo->exists('key111'));
            $this->assertTrue($writeRepo->exists('key111'));
        }
    }