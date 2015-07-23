<?php

    namespace Neoform\Entity\Repo\MetaCache;

    use Neoform;

    class Test extends Neoform\Test\Model {

        public function init() {
            $repo = new Neoform\Entity\Repo\MetaCache\Driver\Redis(Neoform\Redis::getService('master'));
            //$repo = Neoform\Entity\Repo\MetaCache\Driver\Memory::getInstance('master');

            $this->repoDriverListAppendlistUnionListRemove($repo);
            $this->repoDriverListAppendMultiListRemoveMulti($repo);
            $this->repoDriverListPull($repo);

            // This only applies to redis or other engines that can batch process
            $cacheRepo = new Neoform\Entity\Repo\Cache\Driver\Redis(Neoform\Redis::getService('master'));
            $repo      = new Neoform\Entity\Repo\MetaCache\Driver\Redis(Neoform\Redis::getService('master'));

            $this->repoDriverBatchListAppendlistUnionListRemove($repo, $cacheRepo);
            $this->repoDriverBatchListAppendMultiListRemoveMulti($repo, $cacheRepo);
            $this->repoDriverBatchListPull($repo, $cacheRepo);
        }

        /**
         * listAppend, listUnion, listRemove
         */
        protected function repoDriverListAppendListUnionListRemove(Driver $repo) {
            // Setup test
            $repo->flush();

            $listKeys = [
                'aaa',
                'bbb',
                'ccc',
            ];
            $cacheKey1 = 'ddd';
            $cacheKey2 = 'eee';

            // Add a cacheKey
            $result = $repo->listAppend($listKeys, $cacheKey1);
            $this->assertEquals($result, 3);

            $result = $repo->listUnion($listKeys);
            $this->assertEquals(count($result), 1);
            $this->assertEquals($result[0], $cacheKey1);

            // Add the same cache key
            $result = $repo->listAppend($listKeys, $cacheKey1);
            $this->assertEquals($result, 0);
            $result = $repo->listAppend($listKeys, $cacheKey2);
            $this->assertEquals($result, 3);

            $result = $repo->listUnion($listKeys);
            $this->assertEquals(count($result), 2);

            $this->assertEquals($result[0], $cacheKey1);
            $this->assertEquals($result[1], $cacheKey2);

            // Remove
            $result = $repo->listRemove('aaa', $cacheKey1);
            $this->assertEquals($result, 1);

            $result = $repo->listUnion($listKeys);
            $this->assertEquals(count($result), 2); // should still report the same

            // Remove the key from the other 2 lists
            $result = $repo->listRemove('bbb', $cacheKey1);
            $this->assertEquals($result, 1);
            $result = $repo->listRemove('ccc', $cacheKey1);
            $this->assertEquals($result, 1);

            $result = $repo->listUnion($listKeys);
            $this->assertEquals(count($result), 1);
        }

        /**
         * listAppendMulti, listRemoveMulti
         */
        protected function repoDriverListAppendMultiListRemoveMulti(Driver $repo) {
            // Setup test
            $repo->flush();

            $cacheKeys = [
                'a' => [
                    'aaa',
                    'bbb',
                    'ccc',
                ],
                'b' => [
                    'aaa',
                    'bbb',
                    'ccc',
                ],
                'c' => [
                    'aaa',
                ],
                'd' => [
                    'ddd',
                ],
            ];

            // Add cacheKeys
            $result = $repo->listAppendMulti($cacheKeys);
            $this->assertEquals($result, 8);

            $result = $repo->listUnion([ 'aaa', 'bbb', ]);
            $this->assertEquals(count($result), 3);
            $this->assertTrue(in_array('a', $result) !== false);
            $this->assertTrue(in_array('b', $result) !== false);
            $this->assertTrue(in_array('c', $result) !== false);

            $result = $repo->listUnion([ 'ccc', 'ddd', ]);
            $this->assertEquals(count($result), 3);
            $this->assertTrue(in_array('a', $result) !== false);
            $this->assertTrue(in_array('b', $result) !== false);
            $this->assertTrue(in_array('d', $result) !== false);

            // Add some more cacheKeys
            $result = $repo->listAppendMulti([
                'e' => [
                    'ddd',
                ],
                'f' => [
                    'eee',
                    'fff',
                ],
            ]);
            $this->assertEquals($result, 3);

            $result = $repo->listUnion([ 'ddd', 'eee', ]);
            $this->assertEquals(count($result), 3);
            $this->assertTrue(in_array('d', $result) !== false);
            $this->assertTrue(in_array('e', $result) !== false);
            $this->assertTrue(in_array('f', $result) !== false);

            $result = $repo->listRemoveMulti('a', [ 'aaa', 'bbb', 'ccc', ]);
            $this->assertEquals($result, 3);

            $result = $repo->listUnion([ 'aaa', 'bbb', 'ccc', ]);
            $this->assertEquals(count($result), 2);
            $this->assertFalse(in_array('a', $result));
        }

        /**
         * Pull
         *
         * @param Driver $repo
         */
        protected function repoDriverListPull(Driver $repo) {
            // Setup test
            $repo->flush();

            $cacheKeys = [
                'a' => [
                    'aaa',
                    'bbb',
                    'ccc',
                ],
                'b' => [
                    'aaa',
                    'bbb',
                    'ccc',
                ],
                'c' => [
                    'aaa',
                ],
                'd' => [
                    'ddd',
                ],
            ];

            // Add cacheKeys
            $result = $repo->listAppendMulti($cacheKeys);
            $this->assertEquals($result, 8);

            $result = $repo->listPull([ 'aaa', 'bbb', ]);
            $this->assertEquals(count($result), 3);
            $this->assertTrue(in_array('a', $result) !== false);
            $this->assertTrue(in_array('b', $result) !== false);
            $this->assertTrue(in_array('c', $result) !== false);

            $result = $repo->listUnion([ 'aaa', 'bbb', ]);
            $this->assertEquals(count($result), 0);
        }

        /**
         * Batch handling of listAppend, listUnion, listRemove
         */
        protected function repoDriverBatchListAppendListUnionListRemove(Driver $repo, Neoform\Entity\Repo\Cache\Driver $cacheRepo) {
            // Setup test
            $repo->flush();

            $listKeys = [
                'aaa',
                'bbb',
                'ccc',
            ];
            $cacheKey1 = 'ddd';
            $cacheKey2 = 'eee';

            // Add a cacheKey
            $cacheRepo->batchStart();
            $result = $repo->listAppend($listKeys, $cacheKey1);
            $this->assertNull($result);
            $result = $cacheRepo->batchExecute();
            $this->assertTrue(is_array($result) && count($result) > 0);

            $cacheRepo->batchStart();
            $result = $repo->listUnion($listKeys);
            $this->assertNull($result);
            $result = $cacheRepo->batchExecute();
            $this->assertTrue(is_array($result));
            $this->assertEquals(count($result[0]), 1);
            $this->assertEquals($result[0][0], $cacheKey1);

            // Add the same cache key
            $result = $repo->listAppend($listKeys, $cacheKey1);
            $this->assertEquals($result, 0);
            $result = $repo->listAppend($listKeys, $cacheKey2);
            $this->assertEquals($result, 3);

            $result = $repo->listUnion($listKeys);
            $this->assertEquals(count($result), 2);

            $this->assertEquals($result[0], $cacheKey1);
            $this->assertEquals($result[1], $cacheKey2);

            // Remove
            $cacheRepo->batchStart();
            $result = $repo->listRemove('aaa', $cacheKey1);
            $this->assertNull($result);
            $result = $cacheRepo->batchExecute();
            $this->assertTrue(is_array($result) && count($result) > 0);

            $result = $repo->listUnion($listKeys);
            $this->assertEquals(count($result), 2); // should still report the same
        }

        /**
         * listAppendMulti, listRemoveMulti
         */
        protected function repoDriverBatchListAppendMultiListRemoveMulti(Driver $repo, Neoform\Entity\Repo\Cache\Driver $cacheRepo) {
            // Setup test
            $repo->flush();

            $cacheKeys = [
                'a' => [
                    'aaa',
                    'bbb',
                    'ccc',
                ],
                'b' => [
                    'aaa',
                    'bbb',
                    'ccc',
                ],
                'c' => [
                    'aaa',
                ],
                'd' => [
                    'ddd',
                ],
            ];

            // Add cacheKeys
            $cacheRepo->batchStart();
            $result = $repo->listAppendMulti($cacheKeys);
            $this->assertNull($result);
            $cacheRepo->batchExecute();

            $result = $repo->listUnion([ 'aaa', 'bbb', ]);
            $this->assertEquals(count($result), 3);
            $this->assertTrue(in_array('a', $result) !== false);
            $this->assertTrue(in_array('b', $result) !== false);
            $this->assertTrue(in_array('c', $result) !== false);

            $result = $repo->listUnion([ 'ccc', 'ddd', ]);
            $this->assertEquals(count($result), 3);
            $this->assertTrue(in_array('a', $result) !== false);
            $this->assertTrue(in_array('b', $result) !== false);
            $this->assertTrue(in_array('d', $result) !== false);

            // Add some more cacheKeys
            $cacheRepo->batchStart();
            $result = $repo->listAppendMulti([
                'e' => [
                    'ddd',
                ],
                'f' => [
                    'eee',
                    'fff',
                ],
            ]);
            $this->assertNull($result);
            $result = $cacheRepo->batchExecute();
            $this->assertTrue(is_array($result) && count($result) > 0);

            $cacheRepo->batchStart();
            $result = $repo->listUnion([ 'ddd', 'eee', ]);
            $this->assertNull($result);
            $result = $cacheRepo->batchExecute();
            $this->assertEquals(count($result[0]), 3);
            $this->assertTrue(in_array('d', $result[0]) !== false);
            $this->assertTrue(in_array('e', $result[0]) !== false);
            $this->assertTrue(in_array('f', $result[0]) !== false);

            $result = $repo->listRemoveMulti('a', [ 'aaa', 'bbb', 'ccc', ]);
            $this->assertEquals($result, 3);

            $cacheRepo->batchStart();
            $result = $repo->listUnion([ 'aaa', 'bbb', 'ccc', ]);
            $this->assertNull($result);
            $result = $cacheRepo->batchExecute();
            $this->assertEquals(count($result[0]), 2);
            $this->assertFalse(in_array('a', $result[0]));
        }

        /**
         * Pull
         *
         * @param Driver $repo
         */
        protected function repoDriverBatchListPull(Driver $repo, Neoform\Entity\Repo\Cache\Driver $cacheRepo) {
            // Setup test
            $repo->flush();

            $cacheKeys = [
                'a' => [
                    'aaa',
                    'bbb',
                    'ccc',
                ],
                'b' => [
                    'aaa',
                    'bbb',
                    'ccc',
                ],
                'c' => [
                    'aaa',
                ],
                'd' => [
                    'ddd',
                ],
            ];

            // Add cacheKeys
            $result = $repo->listAppendMulti($cacheKeys);
            $this->assertEquals($result, 8);

            $cacheRepo->batchStart();

            try {
                $result = $repo->listPull([ 'aaa', 'bbb', ]);
                $this->assertEquals(count($result), 3);
                $cacheRepo->batchExecute();

                $this->assertEquals(count($result), 3);
                $this->assertTrue(in_array('a', $result) !== false);
                $this->assertTrue(in_array('b', $result) !== false);
                $this->assertTrue(in_array('c', $result) !== false);

                $result = $repo->listUnion([ 'aaa', 'bbb', ]);
                $this->assertEquals(count($result), 0);

            } catch (Neoform\Entity\Repo\Exception $e) {
                $cacheRepo->batchExecute();
                $this->assertTrue(true);
            }
        }
    }