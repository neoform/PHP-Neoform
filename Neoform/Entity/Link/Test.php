<?php

    namespace Neoform\Entity\Link;

    use Neoform;

    class Test extends Neoform\Test\Model {

        public function init() {

            $config = new Neoform\Entity\Config\Overridden([
                'source_engine'             => 'MySQL',
                'source_engine_pool_read'   => 'master',
                'source_engine_pool_write'  => 'master',
                'source_engine_ttl'         => null,
                'cache_engine'              => 'Redis',
                'cache_engine_pool_read'    => 'master',
                'cache_engine_pool_write'   => 'master',
                'cache_meta_engine'         => 'Redis',
                'cache_meta_engine_pool'    => 'master',
                'cache_delete_expire_ttl'   => null,
                'cache_use_binary_keys'     => true,
            ]);

            $dao = new Neoform\Entity\Link\Test\Dao($config);

            $this->daoInsert($dao);
            $this->daoInsertMulti($dao);
            $this->daoUpdate($dao);
            $this->daoDelete($dao);
            $this->daoDeleteMulti($dao);

            $this->daoCount($dao);
            $this->daoByField($dao);
            $this->daoByFieldMulti($dao);
        }

        protected function daoInsert(Neoform\Entity\Link\Test\Dao $dao) {

            // insert(
            //      array $info,
            //      $replace
            // )

            // Delete first
            $result = $dao->delete([
                'user_id' => 1,
                'site_id' => 1,
            ]);
            $this->assertTrue($result);

            // Insert
            $result = $dao->insert(
                [
                    'user_id' => 1,
                    'site_id' => 1,
                ],
                false
            );
            $this->assertTrue($result);

            // Try again with the same info - this should fail
            $result = $dao->insert(
                [
                    'user_id' => 1,
                    'site_id' => 1,
                ],
                false
            );
            $this->assertFalse($result);

            // Replace
            // Try again with the same info - this should fail
            $result = $dao->insert(
                [
                    'user_id' => 1,
                    'site_id' => 1,
                ],
                true
            );
            $this->assertTrue($result);
        }

        protected function daoInsertMulti(Neoform\Entity\Link\Test\Dao $dao) {

            // insertMulti(
            //      array $infos,
            //      $return
            // )

            // Delete first
            $result = $dao->delete([
                'user_id' => 1,
                'site_id' => 1,
            ]);
            $this->assertTrue($result);
            $result = $dao->delete([
                'user_id' => 2,
                'site_id' => 1,
            ]);
            $this->assertTrue($result);
            $result = $dao->delete([
                'user_id' => 3,
                'site_id' => 1,
            ]);
            $this->assertTrue($result);

            // Insert
            $result = $dao->insertMulti(
                [
                    [
                        'user_id' => 1,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 2,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 3,
                        'site_id' => 1,
                    ],
                ],
                false
            );
            $this->assertTrue($result);

            // Try again with the same info - this should fail
            $result = $dao->insertMulti(
                [
                    [
                        'user_id' => 1,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 2,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 3,
                        'site_id' => 1,
                    ],
                ],
                false
            );
            $this->assertFalse($result);

            // Replace
            // Try again with the same info - this should fail
            $result = $dao->insertMulti(
                [
                    [
                        'user_id' => 1,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 2,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 3,
                        'site_id' => 1,
                    ],
                ],
                true
            );
            $this->assertTrue($result);
        }

        protected function daoUpdate(Neoform\Entity\Link\Test\Dao $dao) {

            // Delete first
            $result = $dao->deleteMulti([
                [ 'user_id' => 1, 'site_id' => 1, ],
                [ 'user_id' => 2, 'site_id' => 1, ],
                [ 'user_id' => 3, 'site_id' => 1, ],
                [ 'user_id' => 4, 'site_id' => 1, ],
            ]);
            $this->assertTrue($result);

            // Insert
            $result = $dao->insertMulti(
                [
                    [
                        'user_id' => 1,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 2,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 3,
                        'site_id' => 1,
                    ],
                ],
                true
            );
            $this->assertTrue($result);
            $this->assertEquals($dao->count([ 'user_id' => 1, ]), 1);
            $this->assertEquals($dao->count([ 'user_id' => 1, ]), 1); // hit cache this time

            // Update
            $result = $dao->update(
                [
                    'user_id' => 4,
                ],
                [
                    'user_id' => 1,
                    'site_id' => 1,
                ]
            );
            $this->assertTrue($result);
            $this->assertEquals($dao->count([ 'user_id' => 1, ]), 0);
            $this->assertEquals($dao->count([ 'user_id' => 1, ]), 0); // hit cache this time
        }

        protected function daoDelete(Neoform\Entity\Link\Test\Dao $dao) {

            // Delete first
            $result = $dao->deleteMulti([
                [ 'user_id' => 1, 'site_id' => 1, ],
                [ 'user_id' => 2, 'site_id' => 1, ],
                [ 'user_id' => 3, 'site_id' => 1, ],
            ]);
            $this->assertTrue($result);

            // Insert
            $result = $dao->insertMulti(
                [
                    [
                        'user_id' => 1,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 2,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 3,
                        'site_id' => 1,
                    ],
                ],
                true
            );
            $this->assertTrue($result);
            $this->assertEquals($dao->count([ 'user_id' => 1, ]), 1);
            $this->assertEquals($dao->count([ 'user_id' => 1, ]), 1); // hit cache this time
            $this->assertEquals($dao->count([ 'user_id' => 3, ]), 1);
            $this->assertEquals($dao->count([ 'user_id' => 3, ]), 1); // hit cache this time

            // Delete
            $result = $dao->deleteMulti([
                [ 'user_id' => 1, 'site_id' => 1, ],
            ]);
            $this->assertTrue($result);

            $this->assertEquals($dao->count([ 'user_id' => 1, ]), 0);
            $this->assertEquals($dao->count([ 'user_id' => 1, ]), 0); // hit cache this time
            $this->assertEquals($dao->count([ 'user_id' => 3, ]), 1);
            $this->assertEquals($dao->count([ 'user_id' => 3, ]), 1); // hit cache this time
        }

        protected function daoDeleteMulti(Neoform\Entity\Link\Test\Dao $dao) {

            // Delete first
            $result = $dao->deleteMulti([
                [ 'user_id' => 1, 'site_id' => 1, ],
                [ 'user_id' => 2, 'site_id' => 1, ],
                [ 'user_id' => 3, 'site_id' => 1, ],
            ]);
            $this->assertTrue($result);

            // Insert
            $result = $dao->insertMulti(
                [
                    [
                        'user_id' => 1,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 2,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 3,
                        'site_id' => 1,
                    ],
                ],
                true
            );
            $this->assertTrue($result);
            $this->assertEquals($dao->count([ 'user_id' => 1, ]), 1);
            $this->assertEquals($dao->count([ 'user_id' => 1, ]), 1); // hit cache this time
            $this->assertEquals($dao->count([ 'user_id' => 2, ]), 1);
            $this->assertEquals($dao->count([ 'user_id' => 2, ]), 1); // hit cache this time
            $this->assertEquals($dao->count([ 'user_id' => 3, ]), 1);
            $this->assertEquals($dao->count([ 'user_id' => 3, ]), 1); // hit cache this time

            // Delete
            $result = $dao->deleteMulti([
                [ 'user_id' => 1, 'site_id' => 1, ],
                [ 'user_id' => 2, 'site_id' => 1, ],
            ]);
            $this->assertTrue($result);
            $this->assertEquals($dao->count([ 'user_id' => 1, ]), 0);
            $this->assertEquals($dao->count([ 'user_id' => 1, ]), 0); // hit cache this time
            $this->assertEquals($dao->count([ 'user_id' => 2, ]), 0);
            $this->assertEquals($dao->count([ 'user_id' => 2, ]), 0); // hit cache this time
            $this->assertEquals($dao->count([ 'user_id' => 3, ]), 1);
            $this->assertEquals($dao->count([ 'user_id' => 3, ]), 1); // hit cache this time
        }

        protected function daoCount(Neoform\Entity\Link\Test\Dao $dao) {

            $result = $dao->deleteMulti([
                [ 'user_id' => 1, 'site_id' => 1, ],
                [ 'user_id' => 2, 'site_id' => 1, ],
                [ 'user_id' => 3, 'site_id' => 1, ],
                [ 'user_id' => 4, 'site_id' => 1, ],
                [ 'user_id' => 5, 'site_id' => 1, ],
            ]);
            $this->assertTrue($result);

            $fullCount       = $dao->count();
            $user1Count      = $dao->count([ 'user_id' => 1, ]);
            $user2Count      = $dao->count([ 'user_id' => 2, ]);
            $user3Count      = $dao->count([ 'user_id' => 3, ]);
            $user4Count      = $dao->count([ 'user_id' => 4, ]);
            $site1Count      = $dao->count([ 'site_id' => 1, ]);
            $site2Count      = $dao->count([ 'site_id' => 2, ]);
            $user1Site1Count = $dao->count([ 'user_id' => 1, 'site_id' => 1, ]);
            $user2Site2Count = $dao->count([ 'user_id' => 2, 'site_id' => 2, ]);
            $user4Site1Count = $dao->count([ 'user_id' => 4, 'site_id' => 1, ]);
            $user4Site2Count = $dao->count([ 'user_id' => 4, 'site_id' => 2, ]);

            $userMultiCountOriginal = $dao->countMulti([
                [ 'user_id' => 0, ], // non-existent
                [ 'user_id' => 1, ], // non-existent
                [ 'user_id' => 2, ], // non-existent
                [ 'user_id' => 3, ], // non-existent
                [ 'user_id' => 4, ], // non-existent
            ]);
            $this->assertEquals(count($userMultiCountOriginal), 5);

            $this->assertEquals($fullCount, 0);
            $this->assertEquals($user1Count, 0);
            $this->assertEquals($user2Count, 0);
            $this->assertEquals($user3Count, 0);
            $this->assertEquals($user4Count, 0);
            $this->assertEquals($site1Count, 0);
            $this->assertEquals($site2Count, 0);
            $this->assertEquals($user1Site1Count, 0);
            $this->assertEquals($user2Site2Count, 0);
            $this->assertEquals($user4Site1Count, 0);
            $this->assertEquals($user4Site2Count, 0);

            $this->assertEquals($userMultiCountOriginal[0], 0);
            $this->assertEquals($userMultiCountOriginal[1], 0);
            $this->assertEquals($userMultiCountOriginal[2], 0);
            $this->assertEquals($userMultiCountOriginal[3], 0);
            $this->assertEquals($userMultiCountOriginal[4], 0);

            // Insert
            $newRowCount = 3;
            $result = $dao->insertMulti(
                [
                    [
                        'user_id' => 1,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 2,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 3,
                        'site_id' => 1,
                    ],
                ],
                true
            );
            $this->assertTrue($result);

            // Recount
            $this->assertEquals($fullCount + $newRowCount, $dao->count());
            $this->assertEquals($user1Count + 1, $dao->count([ 'user_id' => 1, ]));
            $this->assertEquals($user2Count + 1, $dao->count([ 'user_id' => 2, ]));
            $this->assertEquals($user3Count + 1, $dao->count([ 'user_id' => 3, ]));
            $this->assertEquals($user4Count, $dao->count([ 'user_id' => 4, ]));
            $this->assertEquals($site1Count + $newRowCount, $dao->count([ 'site_id' => 1, ]));
            $this->assertEquals($site2Count, $dao->count([ 'site_id' => 2, ]));
            $this->assertEquals($user1Site1Count + 1, $dao->count([ 'user_id' => 1, 'site_id' => 1, ]));
            $this->assertEquals($user2Site2Count, $dao->count([ 'user_id' => 2, 'site_id' => 2, ]));
            $this->assertEquals($user4Site1Count, $dao->count([ 'user_id' => 4, 'site_id' => 1, ]));
            $this->assertEquals($user4Site2Count, $dao->count([ 'user_id' => 4, 'site_id' => 2, ]));

            $userMultiCountOriginalInserted = $dao->countMulti([
                [ 'user_id' => 0, ], // non-existent
                [ 'user_id' => 1, ],
                [ 'user_id' => 3, ],
                [ 'user_id' => 2, ],
                [ 'user_id' => 4, ], // non-existent
            ]);
            $this->assertEquals(count($userMultiCountOriginalInserted), 5);

            $this->assertEquals($userMultiCountOriginal[0], $userMultiCountOriginalInserted[0]);
            $this->assertEquals($userMultiCountOriginal[1] + 1, $userMultiCountOriginalInserted[1]);
            $this->assertEquals($userMultiCountOriginal[2] + 1, $userMultiCountOriginalInserted[2]);
            $this->assertEquals($userMultiCountOriginal[3] + 1, $userMultiCountOriginalInserted[3]);
            $this->assertEquals($userMultiCountOriginal[4], $userMultiCountOriginalInserted[4]);

            // Update
            $result = $dao->update(
                [
                    'user_id' => 4,
                ],
                [
                    'user_id' => 1,
                    'site_id' => 1,
                ]
            );
            $this->assertTrue($result);

            // Recount
            $this->assertEquals($fullCount + $newRowCount, $dao->count());
            $this->assertEquals($user1Count, $dao->count([ 'user_id' => 1, ]));
            $this->assertEquals($user2Count + 1, $dao->count([ 'user_id' => 2, ]));
            $this->assertEquals($user2Count + 1, $dao->count([ 'user_id' => 2, ]));
            $this->assertEquals($site1Count + $newRowCount, $dao->count([ 'site_id' => 1, ]));
            $this->assertEquals($site2Count, $dao->count([ 'site_id' => 2, ]));
            $this->assertEquals($user1Site1Count, $dao->count([ 'user_id' => 1, 'site_id' => 1, ]));
            $this->assertEquals($user2Site2Count, $dao->count([ 'user_id' => 2, 'site_id' => 2, ]));
            $this->assertEquals($user4Site1Count + 1, $dao->count([ 'user_id' => 4, 'site_id' => 1, ]));
            $this->assertEquals($user4Site2Count, $dao->count([ 'user_id' => 4, 'site_id' => 2, ]));

            $userMultiCountOriginalInserted = $dao->countMulti([
                [ 'user_id' => 0, ], // non-existent
                [ 'user_id' => 1, ], // non-existent
                [ 'user_id' => 3, ],
                [ 'user_id' => 2, ],
                [ 'user_id' => 4, ],
            ]);
            $this->assertEquals(count($userMultiCountOriginalInserted), 5);

            $this->assertEquals($userMultiCountOriginal[0], $userMultiCountOriginalInserted[0]);
            $this->assertEquals($userMultiCountOriginal[1], $userMultiCountOriginalInserted[1]);
            $this->assertEquals($userMultiCountOriginal[2] + 1, $userMultiCountOriginalInserted[2]);
            $this->assertEquals($userMultiCountOriginal[3] + 1, $userMultiCountOriginalInserted[2]);
            $this->assertEquals($userMultiCountOriginal[4] + 1, $userMultiCountOriginalInserted[3]);
        }


        protected function daoByField(Neoform\Entity\Link\Test\Dao $dao) {

            $result = $dao->deleteMulti([
                [ 'user_id' => 1, 'site_id' => 1, ],
                [ 'user_id' => 2, 'site_id' => 1, ],
                [ 'user_id' => 3, 'site_id' => 1, ],
                [ 'user_id' => 4, 'site_id' => 1, ],
                [ 'user_id' => 5, 'site_id' => 1, ],
            ]);
            $this->assertTrue($result);

            $result = $dao->insertMulti(
                [
                    [
                        'user_id' => 1,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 2,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 3,
                        'site_id' => 1,
                    ],
                ],
                true
            );
            $this->assertTrue($result);

            // status: 0 Asc 0, 5
            $userIds = $dao->by_site(0, [ 'id' => Neoform\Entity\Dao::SORT_ASC, ], 0, 5);

            $this->assertTrue(is_array($userIds));
            $this->assertEquals(count($userIds), 0);

            // status: 1 Asc 0, 5
            $userIds = $dao->by_site(1, [ 'id' => Neoform\Entity\Dao::SORT_ASC, ], 0, 5);

            $this->assertTrue(is_array($userIds));
            $this->assertEquals(count($userIds), 3);

            $this->assertEquals((int) $userIds[0], 1);
            $this->assertEquals((int) $userIds[1], 2);
            $this->assertEquals((int) $userIds[2], 3);

            // status: 1 Asc 2, 5
            $userIds = $dao->by_site(1, [ 'id' => Neoform\Entity\Dao::SORT_ASC, ], 2, 5);

            $this->assertTrue(is_array($userIds));
            $this->assertEquals(count($userIds), 1);

            $this->assertEquals((int) $userIds[0], 3);
        }


        protected function daoByFieldMulti(Neoform\Entity\Link\Test\Dao $dao) {

            $result = $dao->deleteMulti([
                [ 'user_id' => 1, 'site_id' => 1, ],
                [ 'user_id' => 2, 'site_id' => 1, ],
                [ 'user_id' => 3, 'site_id' => 1, ],
                [ 'user_id' => 4, 'site_id' => 1, ],
                [ 'user_id' => 5, 'site_id' => 1, ],
            ]);
            $this->assertTrue($result);

            $result = $dao->insertMulti(
                [
                    [
                        'user_id' => 1,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 2,
                        'site_id' => 1,
                    ],
                    [
                        'user_id' => 3,
                        'site_id' => 1,
                    ],
                ],
                true
            );
            $this->assertTrue($result);

            $userIdsArr = $dao->by_site_multi(
                [0, 1, 2],
                [ 'id' => Neoform\Entity\Dao::SORT_ASC, ],
                0,
                5
            );

            $this->assertTrue(is_array($userIdsArr));
            $this->assertEquals(count($userIdsArr), 3);

            $this->assertTrue(is_array($userIdsArr[0]));
            $this->assertEquals(count($userIdsArr[0]), 0);
            $this->assertTrue(is_array($userIdsArr[1]));
            $this->assertEquals(count($userIdsArr[1]), 3);
            $this->assertTrue(is_array($userIdsArr[2]));
            $this->assertEquals(count($userIdsArr[2]), 0);

            $this->assertEquals((int) $userIdsArr[1][0], 1);
            $this->assertEquals((int) $userIdsArr[1][1], 2);
            $this->assertEquals((int) $userIdsArr[1][2], 3);
        }
    }