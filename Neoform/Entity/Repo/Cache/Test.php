<?php

    namespace Neoform\Entity\Repo\Cache;

    use Neoform;

    class Test extends Neoform\Test\Model {

        public function init() {
            $repo = new Neoform\Entity\Repo\Cache\Driver\Redis(Neoform\Redis::getService('master'));
            //$repo = new Neoform\Entity\Repo\Cache\Driver\Memcache(Neoform\Memcache::getService('master'));
            //$repo = Neoform\Entity\Repo\Cache\Driver\Memory::getInstance('master');

            $this->repoDriverSetGetExistsDelete($repo);
            $this->repoDriverBinaryKeys($repo);
            $this->repoDriverSetMultiGetMultiDeleteMulti($repo);
            $this->repoDriverExpire($repo);
        }

        /**
         * Set/Get/Exists/Delete
         */
        protected function repoDriverSetGetExistsDelete(Driver $repo) {
            // Setup test
            $repo->flush();

            $this->assertTrue(! $repo->exists('key1'));
            $this->assertTrue(! $repo->exists('key2'));

            $result = $repo->set('key1', 'hello');
            $this->assertTrue($result === 1);
            $this->assertTrue($repo->exists('key1'));
            $this->assertTrue($repo->get('key1')[0] === 'hello');

            $result = $repo->delete('key1');
            $this->assertTrue($result === 1);
            $this->assertTrue(! $repo->exists('key1'));
            $this->assertTrue($repo->get('key1')[0] === null);

            $this->assertTrue(! $repo->exists('key2'));
        }

        /**
         * Set/Get/Exists/Delete with binary key
         *
         * memcached will fail this test
         */
        protected function repoDriverBinaryKeys(Driver $repo) {
            // Setup test
            $repo->flush();

            $key1 = md5('hello1', true);
            $key2 = md5('hello2', true);

            $this->assertTrue(! $repo->exists($key1));
            $this->assertTrue(! $repo->exists($key2));

            $result = $repo->set($key1, 'hello');
            $this->assertTrue($result === 1);
            $this->assertTrue($repo->exists($key1));
            $this->assertTrue($repo->get($key1)[0] === 'hello');

            $result = $repo->delete($key1);
            $this->assertTrue($result === 1);
            $this->assertTrue(! $repo->exists($key1));
            $this->assertTrue($repo->get($key1)[0] === null);

            $this->assertTrue(! $repo->exists($key2));
        }

        /**
         * SetMulti\GetMulti
         */
        protected function repoDriverSetMultiGetMultiDeleteMulti(Driver $repo) {
            // Setup test
            $repo->flush();

            $this->assertTrue(! $repo->exists('key1'));
            $this->assertTrue(! $repo->exists('key2'));
            $this->assertTrue(! $repo->exists('key3'));
            $this->assertTrue(! $repo->exists('key4'));

            $result = $repo->setMulti([
                'key1' => 111,
                'key2' => 222,
                'key3' => 333,
                'key4' => 444,
            ]);
            $this->assertTrue($result === 4);
            $this->assertTrue($repo->exists('key1'));
            $this->assertTrue($repo->exists('key2'));
            $this->assertTrue($repo->exists('key3'));
            $this->assertTrue($repo->exists('key4'));

            $results = $repo->getMulti([
                5   => 'key1',
                7   => 'key2',
                'b' => 'key3',
                9   => 'key4',
            ]);
            $this->assertTrue(count($results) === 4);

            // Keys need to be preserved
            $this->assertTrue($results[5] === 111);
            $this->assertTrue($results[7] === 222);
            $this->assertTrue($results['b'] === 333);
            $this->assertTrue($results[9] === 444);

            // Make sure the order has also been preserved
            $this->assertTrue(array_keys($results)[0] === 5);
            $this->assertTrue(array_keys($results)[1] === 7);
            $this->assertTrue(array_keys($results)[2] === 'b');
            $this->assertTrue(array_keys($results)[3] === 9);

            // Delete two of the keys
            $results = $repo->deleteMulti([
                'key1',
                'key3',
            ]);
            $this->assertTrue($results === 2);

            // Try fetching again, this time only have 2 row returned
            $results = $repo->getMulti([
                5   => 'key1',
                7   => 'key2',
                'b' => 'key3',
                9   => 'key4',
            ]);

            $this->assertTrue(count($results) === 2);

            // Keys need to be preserved
            $this->assertTrue(! array_key_exists(5, $results));
            $this->assertTrue($results[7] === 222);
            $this->assertTrue(! array_key_exists('b', $results));
            $this->assertTrue($results[9] === 444);

            // Make sure the order has also been preserved
            $this->assertTrue(array_keys($results)[0] === 7);
            $this->assertTrue(array_keys($results)[1] === 9);
        }

        /**
         * Set/Get/Exists/Delete
         */
        protected function repoDriverExpire(Driver $repo) {
            // Setup test
            $repo->flush();

            $this->assertTrue(! $repo->exists('key1'));
            $this->assertTrue(! $repo->exists('key2'));

            $result = $repo->set('key1', 111);
            $this->assertTrue($result === 1);
            $result = $repo->set('key2', 222);
            $this->assertTrue($result === 1);
            $result = $repo->set('key3', 333);
            $this->assertTrue($result === 1);

            $this->assertTrue($repo->exists('key1'));
            $this->assertTrue($repo->exists('key2'));
            $this->assertTrue($repo->exists('key3'));
            $this->assertTrue($repo->get('key1')[0] === 111);
            $this->assertTrue($repo->get('key2')[0] === 222);
            $this->assertTrue($repo->get('key3')[0] === 333);

            $result = $repo->expire('key1', 1);
            $this->assertTrue($result === 1);
            sleep(2);
            $this->assertTrue(! $repo->exists('key1'));
            $this->assertTrue($repo->exists('key2'));
            $this->assertTrue($repo->exists('key3'));

            $result = $repo->expireMulti(['key2', 'key3', ], 1);
            $this->assertTrue($result === 2);
            sleep(2);
            $this->assertTrue(! $repo->exists('key1'));
            $this->assertTrue(! $repo->exists('key2'));
            $this->assertTrue(! $repo->exists('key3'));
        }
    }