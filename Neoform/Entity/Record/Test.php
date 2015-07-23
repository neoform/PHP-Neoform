<?php

    namespace Neoform\Entity\Record;

    use Neoform;

    class Test extends Neoform\Test\Model {

        public function init() {

            $config = new Neoform\Entity\Config\Overridden([
                'source_engine'             => 'MySQL',
                'source_engine_pool_read'   => 'master',
                'source_engine_pool_write'  => 'master',
                'source_engine_ttl'         => null,
                'cache_engine'              => 'Memory',
                'cache_engine_pool_read'    => 'master',
                'cache_engine_pool_write'   => 'master',
                'cache_meta_engine'         => 'Memory',
                'cache_meta_engine_pool'    => 'master',
                'cache_delete_expire_ttl'   => null,
                'cache_use_binary_keys'     => true,
            ]);

            $dao = new Neoform\Entity\Record\Test\Dao($config);

            $this->daoInsert($dao);
            $this->daoInsertMulti($dao);
            $this->daoUpdate($dao);
            $this->daoDelete($dao);
            $this->daoDeleteMulti($dao);

            $this->daoRecord($dao);
            $this->daoRecords($dao);

            $this->daoCount($dao);
            $this->daoLimit($dao);
            $this->daoByField($dao);
            $this->daoByFieldUnique($dao);
            $this->daoByFieldMulti($dao);
        }

        protected function daoInsert(Neoform\Entity\Record\Test\Dao $dao) {

            // insert(
            //      array $info,
            //      $replace,
            //      $returnModel,
            //      $loadModelFromSource
            // )

            $email = sha1(mt_rand()) . '@example.com';

            // Return bool
            $result = $dao->insert(
                [
                    'email'               => $email,
                    'password_hash'       => 'aaa',
                    'password_hashmethod' => 1,
                    'password_cost'       => 50,
                    'password_salt'       => 'bbb',
                    'status_id'           => 1,
                ],
                false,
                false,
                false
            );
            $this->assertTrue($result);

            // Try again with the same info - this should fail
            $result = $dao->insert(
                [
                    'email'               => $email,
                    'password_hash'       => 'aaa',
                    'password_hashmethod' => 1,
                    'password_cost'       => 50,
                    'password_salt'       => 'bbb',
                    'status_id'           => 1,
                ],
                false,
                false,
                false
            );
            $this->assertFalse($result);

            // Return model
            $email = sha1(mt_rand()) . '@example.com';
            $result = $dao->insert(
                [
                    'email'               => $email,
                    'password_hash'       => 'aaa',
                    'password_hashmethod' => 1,
                    'password_cost'       => 50,
                    'password_salt'       => 'bbb',
                    'status_id'           => 1,
                ],
                false,
                true,
                false
            );
            $this->assertTrue($result instanceof Neoform\User\Model);
            $this->assertTrue((bool) $result->id);
            $this->assertEquals($result->email, $email);
            $this->assertEquals($result->password_hash, 'aaa');
            $this->assertEquals($result->password_hashmethod, 1);
            $this->assertEquals($result->password_cost, 50);
            $this->assertEquals($result->password_salt, 'bbb');
            $this->assertEquals($result->status_id, 1);

            // Return model, loaded from source
            $email = sha1(mt_rand()) . '@example.com';
            $result = $dao->insert(
                [
                    'email'               => $email,
                    'password_hash'       => 'aaa',
                    'password_hashmethod' => 1,
                    'password_cost'       => 50,
                    'password_salt'       => 'bbb',
                    'status_id'           => 1,
                ],
                false,
                true,
                true
            );
            $this->assertTrue($result instanceof Neoform\User\Model);
            $this->assertTrue((bool) $result->id);
            $this->assertEquals($result->email, $email);
            $this->assertEquals($result->password_hash, 'aaa');
            $this->assertEquals($result->password_hashmethod, 1);
            $this->assertEquals((int) $result->password_cost, 50);
            $this->assertEquals($result->password_salt, 'bbb');
            $this->assertEquals((int) $result->status_id, 1);

            // Load model with no cache
            $info = $this->loadInfoNoCache($result->id);
            $this->assertTrue(is_array($info));
            $this->assertTrue(array_key_exists('id', $info));
            $this->assertTrue(array_key_exists('email', $info));
            $this->assertTrue(array_key_exists('password_hash', $info));
            $this->assertTrue(array_key_exists('password_hashmethod', $info));
            $this->assertTrue(array_key_exists('password_cost', $info));
            $this->assertTrue(array_key_exists('password_salt', $info));
            $this->assertTrue(array_key_exists('status_id', $info));

            $this->assertEquals((int) $info['id'], $result->id);
            $this->assertEquals($info['email'], $email);
            $this->assertEquals($info['password_hash'], 'aaa');
            $this->assertEquals((int) $info['password_hashmethod'], 1);
            $this->assertEquals((int) $info['password_cost'], 50);
            $this->assertEquals($info['password_salt'], 'bbb');
            $this->assertEquals((int) $info['status_id'], 1);

            // Load model with cache
            $info = $this->loadInfoWithCache($result->id);
            $this->assertTrue(is_array($info));
            $this->assertTrue(array_key_exists('id', $info));
            $this->assertTrue(array_key_exists('email', $info));
            $this->assertTrue(array_key_exists('password_hash', $info));
            $this->assertTrue(array_key_exists('password_hashmethod', $info));
            $this->assertTrue(array_key_exists('password_cost', $info));
            $this->assertTrue(array_key_exists('password_salt', $info));
            $this->assertTrue(array_key_exists('status_id', $info));

            $this->assertEquals((int) $info['id'], $result->id);
            $this->assertEquals($info['email'], $email);
            $this->assertEquals($info['password_hash'], 'aaa');
            $this->assertEquals((int) $info['password_hashmethod'], 1);
            $this->assertEquals((int) $info['password_cost'], 50);
            $this->assertEquals($info['password_salt'], 'bbb');
            $this->assertEquals((int) $info['status_id'], 1);

            // Load model from cache, do it a second time, for good measure
            $info = $this->loadInfoWithCache($result->id);
            $this->assertTrue(is_array($info));
            $this->assertTrue(array_key_exists('id', $info));
            $this->assertTrue(array_key_exists('email', $info));
            $this->assertTrue(array_key_exists('password_hash', $info));
            $this->assertTrue(array_key_exists('password_hashmethod', $info));
            $this->assertTrue(array_key_exists('password_cost', $info));
            $this->assertTrue(array_key_exists('password_salt', $info));
            $this->assertTrue(array_key_exists('status_id', $info));

            $this->assertEquals((int) $info['id'], $result->id);
            $this->assertEquals($info['email'], $email);
            $this->assertEquals($info['password_hash'], 'aaa');
            $this->assertEquals((int) $info['password_hashmethod'], 1);
            $this->assertEquals((int) $info['password_cost'], 50);
            $this->assertEquals($info['password_salt'], 'bbb');
            $this->assertEquals((int) $info['status_id'], 1);
        }

        protected function daoInsertMulti(Neoform\Entity\Record\Test\Dao $dao) {

            // insertMulti(
            //      array $infos,
            //      $keysMatch,
            //      $return,
            //      $returnCollection,
            //      $loadModelsFromSource
            // )

            /**
             * Return bool
             */
            $email1 = sha1(mt_rand()) . '@example.com';
            $email2 = sha1(mt_rand()) . '@example.com';
            $email3 = sha1(mt_rand()) . '@example.com';

            $result = $dao->insertMulti(
                [
                    [
                        'email'               => $email1,
                        'password_hash'       => 'a1',
                        'password_hashmethod' => 1,
                        'password_cost'       => 50,
                        'password_salt'       => 'b1',
                        'status_id'           => 1,
                    ],
                    [
                        'email'               => $email2,
                        'password_hash'       => 'a2',
                        'password_hashmethod' => 1,
                        'password_cost'       => 30,
                        'password_salt'       => 'b2',
                        'status_id'           => 2,
                    ],
                    [
                        'email'               => $email3,
                        'password_hash'       => 'a3',
                        'password_hashmethod' => 1,
                        'password_cost'       => 30,
                        'password_salt'       => 'b3',
                        'status_id'           => 2,
                    ],
                ],
                true,
                false,
                false,
                false
            );
            $this->assertTrue($result);

            /**
             * Return collection
             */
            $email1 = sha1(mt_rand()) . '@example.com';
            $email2 = sha1(mt_rand()) . '@example.com';
            $email3 = sha1(mt_rand()) . '@example.com';

            $result = $dao->insertMulti(
                [
                    [
                        'email'               => $email1,
                        'password_hash'       => 'a1',
                        'password_hashmethod' => 1,
                        'password_cost'       => 50,
                        'password_salt'       => 'b1',
                        'status_id'           => 1,
                    ],
                    [
                        'email'               => $email2,
                        'password_hash'       => 'a2',
                        'password_hashmethod' => 1,
                        'password_cost'       => 30,
                        'password_salt'       => 'b2',
                        'status_id'           => 2,
                    ],
                    [
                        'email'               => $email3,
                        'password_hash'       => 'a3',
                        'password_hashmethod' => 1,
                        'password_cost'       => 30,
                        'password_salt'       => 'b3',
                        'status_id'           => 2,
                    ],
                ],
                true,
                true,
                true,
                false
            );
            $this->assertTrue($result instanceof Neoform\User\Collection);
            $this->assertEquals(count($result), 3);

            $this->assertTrue((bool) $result[0]->id);
            $this->assertEquals($result[0]->email, $email1);
            $this->assertEquals($result[0]->password_hash, 'a1');
            $this->assertEquals($result[0]->password_hashmethod, 1);
            $this->assertEquals($result[0]->password_cost, 50);
            $this->assertEquals($result[0]->password_salt, 'b1');
            $this->assertEquals($result[0]->status_id, 1);

            $this->assertTrue((bool) $result[1]->id);
            $this->assertEquals($result[1]->email, $email2);
            $this->assertEquals($result[1]->password_hash, 'a2');
            $this->assertEquals($result[1]->password_hashmethod, 1);
            $this->assertEquals($result[1]->password_cost, 30);
            $this->assertEquals($result[1]->password_salt, 'b2');
            $this->assertEquals($result[1]->status_id, 2);

            $this->assertTrue((bool) $result[2]->id);
            $this->assertEquals($result[2]->email, $email3);
            $this->assertEquals($result[2]->password_hash, 'a3');
            $this->assertEquals($result[2]->password_hashmethod, 1);
            $this->assertEquals($result[2]->password_cost, 30);
            $this->assertEquals($result[2]->password_salt, 'b3');
            $this->assertEquals($result[2]->status_id, 2);


            /**
             * Return array of info
             */
            $email1 = sha1(mt_rand()) . '@example.com';
            $email2 = sha1(mt_rand()) . '@example.com';
            $email3 = sha1(mt_rand()) . '@example.com';

            $result = $dao->insertMulti(
                [
                    [
                        'email'               => $email1,
                        'password_hash'       => 'a1',
                        'password_hashmethod' => 1,
                        'password_cost'       => 50,
                        'password_salt'       => 'b1',
                        'status_id'           => 1,
                    ],
                    [
                        'email'               => $email2,
                        'password_hash'       => 'a2',
                        'password_hashmethod' => 1,
                        'password_cost'       => 30,
                        'password_salt'       => 'b2',
                        'status_id'           => 2,
                    ],
                    [
                        'email'               => $email3,
                        'password_hash'       => 'a3',
                        'password_hashmethod' => 1,
                        'password_cost'       => 30,
                        'password_salt'       => 'b3',
                        'status_id'           => 2,
                    ],
                ],
                true,
                false,
                false,
                true
            );
            $this->assertTrue(is_array($result));
            $this->assertEquals(count($result), 3);

            $this->assertTrue((bool) $result[0]['id']);
            $this->assertEquals($result[0]['email'], $email1);
            $this->assertEquals($result[0]['password_hash'], 'a1');
            $this->assertEquals((int) $result[0]['password_hashmethod'], 1);
            $this->assertEquals((int) $result[0]['password_cost'], 50);
            $this->assertEquals($result[0]['password_salt'], 'b1');
            $this->assertEquals((int) $result[0]['status_id'], 1);

            $this->assertTrue((bool) $result[1]['id']);
            $this->assertEquals($result[1]['email'], $email2);
            $this->assertEquals($result[1]['password_hash'], 'a2');
            $this->assertEquals((int) $result[1]['password_hashmethod'], 1);
            $this->assertEquals((int) $result[1]['password_cost'], 30);
            $this->assertEquals($result[1]['password_salt'], 'b2');
            $this->assertEquals((int) $result[1]['status_id'], 2);

            $this->assertTrue((bool) $result[2]['id']);
            $this->assertEquals($result[2]['email'], $email3);
            $this->assertEquals($result[2]['password_hash'], 'a3');
            $this->assertEquals((int) $result[2]['password_hashmethod'], 1);
            $this->assertEquals((int) $result[2]['password_cost'], 30);
            $this->assertEquals($result[2]['password_salt'], 'b3');
            $this->assertEquals((int) $result[2]['status_id'], 2);

            /**
             * Reload from source
             */
            $email1 = sha1(mt_rand()) . '@example.com';
            $email2 = sha1(mt_rand()) . '@example.com';
            $email3 = sha1(mt_rand()) . '@example.com';

            $result = $dao->insertMulti(
                [
                    [
                        'email'               => $email1,
                        'password_hash'       => 'a1',
                        'password_hashmethod' => 1,
                        'password_cost'       => 50,
                        'password_salt'       => 'b1',
                        'status_id'           => 1,
                    ],
                    [
                        'email'               => $email2,
                        'password_hash'       => 'a2',
                        'password_hashmethod' => 1,
                        'password_cost'       => 30,
                        'password_salt'       => 'b2',
                        'status_id'           => 2,
                    ],
                    [
                        'email'               => $email3,
                        'password_hash'       => 'a3',
                        'password_hashmethod' => 1,
                        'password_cost'       => 30,
                        'password_salt'       => 'b3',
                        'status_id'           => 2,
                    ],
                ],
                true,
                true,
                true,
                true
            );
            $this->assertTrue($result instanceof Neoform\User\Collection);
            $this->assertEquals(count($result), 3);

            $this->assertTrue((bool) $result[0]->id);
            $this->assertEquals($result[0]->email, $email1);
            $this->assertEquals($result[0]->password_hash, 'a1');
            $this->assertEquals($result[0]->password_hashmethod, 1);
            $this->assertEquals($result[0]->password_cost, 50);
            $this->assertEquals($result[0]->password_salt, 'b1');
            $this->assertEquals($result[0]->status_id, 1);

            $this->assertTrue((bool) $result[1]->id);
            $this->assertEquals($result[1]->email, $email2);
            $this->assertEquals($result[1]->password_hash, 'a2');
            $this->assertEquals($result[1]->password_hashmethod, 1);
            $this->assertEquals($result[1]->password_cost, 30);
            $this->assertEquals($result[1]->password_salt, 'b2');
            $this->assertEquals($result[1]->status_id, 2);

            $this->assertTrue((bool) $result[2]->id);
            $this->assertEquals($result[2]->email, $email3);
            $this->assertEquals($result[2]->password_hash, 'a3');
            $this->assertEquals($result[2]->password_hashmethod, 1);
            $this->assertEquals($result[2]->password_cost, 30);
            $this->assertEquals($result[2]->password_salt, 'b3');
            $this->assertEquals($result[2]->status_id, 2);
        }

        protected function daoUpdate(Neoform\Entity\Record\Test\Dao $dao) {

            // update(
            //      Model $user,
            //      array $info,
            //      $returnModel,
            //      $loadModelFromSource)
            // )

            $email1 = sha1(mt_rand()) . '@example.com';
            $email2 = sha1(mt_rand()) . '@example.com';
            $email3 = sha1(mt_rand()) . '@example.com';

            // Create new record - return a model
            $model = $dao->insert(
                [
                    'email'               => $email1,
                    'password_hash'       => 'aaa',
                    'password_hashmethod' => 1,
                    'password_cost'       => 50,
                    'password_salt'       => 'bbb',
                    'status_id'           => 1,
                ],
                false,
                true,
                true
            );
            $this->assertTrue($model instanceof Neoform\User\Model);
            $this->assertTrue((bool) $model->id);
            $this->assertEquals($model->email, $email1);
            $this->assertEquals($model->password_hash, 'aaa');
            $this->assertEquals($model->password_hashmethod, 1);
            $this->assertEquals((int) $model->password_cost, 50);
            $this->assertEquals($model->password_salt, 'bbb');
            $this->assertEquals((int) $model->status_id, 1);

            // Update
            $result = $dao->update(
                $model,
                [
                    'email'               => $email2,
                    'password_hash'       => 'hello',
                    'password_hashmethod' => 1,
                    'password_cost'       => 55,
                    'password_salt'       => 'bye',
                    'status_id'           => 2,
                ],
                false,
                false
            );
            $this->assertTrue($result);

            $updatedModel = Neoform\User\Model::fromArray($this->loadInfoWithCache($model->id));

            $this->assertTrue($updatedModel instanceof Neoform\User\Model);
            $this->assertTrue((bool) $updatedModel->id);
            $this->assertEquals($updatedModel->email, $email2);
            $this->assertEquals($updatedModel->password_hash, 'hello');
            $this->assertEquals($updatedModel->password_hashmethod, 1);
            $this->assertEquals((int) $updatedModel->password_cost, 55);
            $this->assertEquals($updatedModel->password_salt, 'bye');
            $this->assertEquals((int) $updatedModel->status_id, 2);

            // Update again, but return model this time
            $updatedModel = $dao->update(
                $model,
                [
                    'email'               => $email3,
                    'password_hash'       => 'hello again',
                    'password_hashmethod' => 1,
                    'password_cost'       => 60,
                    'password_salt'       => 'bye again',
                    'status_id'           => 1,
                ],
                true,
                false
            );
            $this->assertTrue($updatedModel instanceof Neoform\User\Model);
            $this->assertTrue((bool) $updatedModel->id);
            $this->assertEquals($updatedModel->email, $email3);
            $this->assertEquals($updatedModel->password_hash, 'hello again');
            $this->assertEquals($updatedModel->password_hashmethod, 1);
            $this->assertEquals((int) $updatedModel->password_cost, 60);
            $this->assertEquals($updatedModel->password_salt, 'bye again');
            $this->assertEquals((int) $updatedModel->status_id, 1);
        }

        protected function daoDelete(Neoform\Entity\Record\Test\Dao $dao) {

            // Return model
            $models = [];
            $pks    = [];
            for ($i=0; $i < 10; $i++) {
                $email  = sha1(mt_rand()) . '@example.com';
                $result = $dao->insert(
                    [
                        'email'               => $email,
                        'password_hash'       => 'aaa',
                        'password_hashmethod' => 1,
                        'password_cost'       => 50,
                        'password_salt'       => 'bbb',
                        'status_id'           => 1,
                    ],
                    false,
                    true,
                    true
                );
                $this->assertTrue($result instanceof Neoform\User\Model);
                $this->assertTrue((bool) $result->id);
                $this->assertEquals($result->email, $email);
                $this->assertEquals($result->password_hash, 'aaa');
                $this->assertEquals($result->password_hashmethod, 1);
                $this->assertEquals($result->password_cost, 50);
                $this->assertEquals($result->password_salt, 'bbb');
                $this->assertEquals($result->status_id, 1);

                $models[] = $result;
                $pks[]    = $result->id;
            }

            foreach ($models as $model) {
                // Delete record
                $result = $dao->delete($model);
                $this->assertTrue($result);

                // Check if record exists - shouldn't
                $result = $dao->record($model->id);
                $this->assertNull($result);

                array_shift($pks);

                // Check other records still exist
                $results = $dao->records($pks);
                $this->assertTrue(is_array($results));
                $this->assertEquals(count($pks), count($results));
            }
        }

        protected function daoDeleteMulti(Neoform\Entity\Record\Test\Dao $dao) {

            // Return model
            $models = [];
            $pks    = [];
            for ($i=0; $i < 10; $i++) {
                $email  = sha1(mt_rand()) . '@example.com';
                $result = $dao->insert(
                    [
                        'email'               => $email,
                        'password_hash'       => 'aaa',
                        'password_hashmethod' => 1,
                        'password_cost'       => 50,
                        'password_salt'       => 'bbb',
                        'status_id'           => 1,
                    ],
                    false,
                    true,
                    true
                );
                $this->assertTrue($result instanceof Neoform\User\Model);
                $this->assertTrue((bool) $result->id);
                $this->assertEquals($result->email, $email);
                $this->assertEquals($result->password_hash, 'aaa');
                $this->assertEquals($result->password_hashmethod, 1);
                $this->assertEquals($result->password_cost, 50);
                $this->assertEquals($result->password_salt, 'bbb');
                $this->assertEquals($result->status_id, 1);

                $models[] = $result;
                $pks[]    = $result->id;
            }

            $collection = Neoform\User\Collection::fromModels($models);

            // Delete record
            $result = $dao->deleteMulti($collection);
            $this->assertTrue($result);

            // Check if records exists - shouldn't
            $result = $dao->records($pks);
            $this->assertTrue(is_array($result));
            $this->assertEquals(count($result), 10);              // 10 results back
            $this->assertEquals(count(array_filter($result)), 0); // all of them empty
        }

        protected function daoRecord(Neoform\Entity\Record\Test\Dao $dao) {

            // Return model
            $models = [];
            $pks    = [];
            for ($i=0; $i < 10; $i++) {
                $email  = sha1(mt_rand()) . '@example.com';
                $result = $dao->insert(
                    [
                        'email'               => $email,
                        'password_hash'       => 'aaa',
                        'password_hashmethod' => 1,
                        'password_cost'       => 50,
                        'password_salt'       => 'bbb',
                        'status_id'           => 1,
                    ],
                    false,
                    true,
                    true
                );
                $this->assertTrue($result instanceof Neoform\User\Model);
                $this->assertTrue((bool) $result->id);
                $this->assertEquals($result->email, $email);
                $this->assertEquals($result->password_hash, 'aaa');
                $this->assertEquals($result->password_hashmethod, 1);
                $this->assertEquals($result->password_cost, 50);
                $this->assertEquals($result->password_salt, 'bbb');
                $this->assertEquals($result->status_id, 1);

                $models[] = $result;
                $pks[]    = $result->id;
            }

            foreach ($models as $model) {
                // Pull record
                $info = $dao->record($model->id);

                $this->assertTrue(is_array($info));
                $this->assertEquals((int) $info['id'], $model->id);
                $this->assertEquals($info['email'], $model->email);
                $this->assertEquals($info['password_hash'], $model->password_hash);
                $this->assertEquals((int) $info['password_hashmethod'], $model->password_hashmethod);
                $this->assertEquals((int) $info['password_cost'], $model->password_cost);
                $this->assertEquals($info['password_salt'], $model->password_salt);
                $this->assertEquals((int) $info['status_id'], $model->status_id);
            }
        }

        protected function daoRecords(Neoform\Entity\Record\Test\Dao $dao) {

            // Return model
            $models = [];
            $pks    = [];
            for ($i=0; $i < 10; $i++) {
                $email  = sha1(mt_rand()) . '@example.com';
                $result = $dao->insert(
                    [
                        'email'               => $email,
                        'password_hash'       => 'aaa',
                        'password_hashmethod' => 1,
                        'password_cost'       => 50,
                        'password_salt'       => 'bbb',
                        'status_id'           => 1,
                    ],
                    false,
                    true,
                    true
                );
                $this->assertTrue($result instanceof Neoform\User\Model);
                $this->assertTrue((bool) $result->id);
                $this->assertEquals($result->email, $email);
                $this->assertEquals($result->password_hash, 'aaa');
                $this->assertEquals($result->password_hashmethod, 1);
                $this->assertEquals($result->password_cost, 50);
                $this->assertEquals($result->password_salt, 'bbb');
                $this->assertEquals($result->status_id, 1);

                $models[] = $result;
                $pks[]    = $result->id;
            }

            $collection = Neoform\User\Collection::fromModels($models);

            // Pull records
            foreach ($dao->records($collection->field('id')) as $k => $info) {
                $this->assertTrue(is_array($info));
                $this->assertEquals((int) $info['id'], $collection[$k]->id);
                $this->assertEquals($info['email'], $collection[$k]->email);
                $this->assertEquals($info['password_hash'], $collection[$k]->password_hash);
                $this->assertEquals((int) $info['password_hashmethod'], $collection[$k]->password_hashmethod);
                $this->assertEquals((int) $info['password_cost'], $collection[$k]->password_cost);
                $this->assertEquals($info['password_salt'], $collection[$k]->password_salt);
                $this->assertEquals((int) $info['status_id'], $collection[$k]->status_id);
            }
        }

        protected function daoCount(Neoform\Entity\Record\Test\Dao $dao) {

            $fullCount                  = $dao->count();
            $status1Count               = $dao->count([ 'status_id' => 1, ]);
            $status2Count               = $dao->count([ 'status_id' => 2, ]);
            $passwordCost50Count        = $dao->count([ 'password_cost' => 50, ]);
            $passwordCost55Count        = $dao->count([ 'password_cost' => 55, ]);
            $status1PasswordCost50Count = $dao->count([ 'status_id' => 1, 'password_cost' => 50, ]);
            $status2PasswordCost55Count = $dao->count([ 'status_id' => 2, 'password_cost' => 55, ]);

            $passwordCostMultiCountOriginal = $dao->countMulti([
                [ 'password_cost' => 50, ],
                [ 'password_cost' => 55, ],
            ]);

            // Insert $newRowCount rows
            $newRowCount = 5;
            $models      = [];
            for ($i=0; $i < $newRowCount; $i++) {
                $email = sha1(mt_rand()) . '@example.com';
                $model = $dao->insert(
                    [
                        'email'               => $email,
                        'password_hash'       => 'aaa',
                        'password_hashmethod' => 1,
                        'password_cost'       => 50,
                        'password_salt'       => 'bbb',
                        'status_id'           => 1,
                    ],
                    false,
                    true,
                    true
                );
                $this->assertTrue($model instanceof Neoform\User\Model);
                $this->assertTrue((bool) $model->id);
                $this->assertEquals($model->email, $email);
                $this->assertEquals($model->password_hash, 'aaa');
                $this->assertEquals($model->password_hashmethod, 1);
                $this->assertEquals($model->password_cost, 50);
                $this->assertEquals($model->password_salt, 'bbb');
                $this->assertEquals($model->status_id, 1);

                $models[] = $model;
            }

            // Recount
            $this->assertEquals($fullCount + $newRowCount, $dao->count());
            $this->assertEquals($status1Count + $newRowCount, $dao->count([ 'status_id' => 1, ]));
            $this->assertEquals($status2Count, $dao->count([ 'status_id' => 2, ]));
            $this->assertEquals($passwordCost50Count + $newRowCount, $dao->count([ 'password_cost' => 50, ]));
            $this->assertEquals($passwordCost55Count, $dao->count([ 'password_cost' => 55, ]));
            $this->assertEquals($status1PasswordCost50Count + $newRowCount, $dao->count([ 'status_id' => 1, 'password_cost' => 50, ]));
            $this->assertEquals($status2PasswordCost55Count, $dao->count([ 'status_id' => 2, 'password_cost' => 55, ]));

            $passwordCostMultiCountInserted = $dao->countMulti([
                [ 'password_cost' => 50, ],
                [ 'password_cost' => 55, ],
            ]);
            $this->assertEquals($passwordCostMultiCountOriginal[0] + $newRowCount, $passwordCostMultiCountInserted[0]);
            $this->assertEquals($passwordCostMultiCountOriginal[1], $passwordCostMultiCountInserted[1]);


            // Update those rows
            foreach ($models as &$model) {
                $email = sha1(mt_rand()) . '@example.com';
                $model = $dao->update(
                    $model,
                    [
                        'email'               => $email,
                        'password_hash'       => 'bbb',
                        'password_hashmethod' => 1,
                        'password_cost'       => 55,
                        'password_salt'       => 'aaa',
                        'status_id'           => 2,
                    ],
                    true,
                    true
                );
                $this->assertTrue($model instanceof Neoform\User\Model);
                $this->assertTrue((bool) $model->id);
                $this->assertEquals($model->email, $email);
                $this->assertEquals($model->password_hash, 'bbb');
                $this->assertEquals($model->password_hashmethod, 1);
                $this->assertEquals($model->password_cost, 55);
                $this->assertEquals($model->password_salt, 'aaa');
                $this->assertEquals($model->status_id, 2);
            }
            unset($model);

            // Recount
            $this->assertEquals($fullCount + $newRowCount, $dao->count());
            $this->assertEquals($status1Count, $dao->count([ 'status_id' => 1, ]));
            $this->assertEquals($status2Count + $newRowCount, $dao->count([ 'status_id' => 2, ]));
            $this->assertEquals($passwordCost50Count, $dao->count([ 'password_cost' => 50, ]));
            $this->assertEquals($passwordCost55Count + $newRowCount, $dao->count([ 'password_cost' => 55, ]));
            $this->assertEquals($status1PasswordCost50Count, $dao->count([ 'status_id' => 1, 'password_cost' => 50, ]));
            $this->assertEquals($status2PasswordCost55Count + $newRowCount, $dao->count([ 'status_id' => 2, 'password_cost' => 55, ]));

            $passwordCostMultiCountUpdated = $dao->countMulti([
                [ 'password_cost' => 50, ],
                [ 'password_cost' => 55, ],
            ]);
            $this->assertEquals($passwordCostMultiCountOriginal[0], $passwordCostMultiCountUpdated[0]);
            $this->assertEquals($passwordCostMultiCountOriginal[1] + $newRowCount, $passwordCostMultiCountUpdated[1]);

            // Delete the rows
            $result = $dao->deleteMulti(Neoform\User\Collection::fromModels($models));
            $this->assertTrue($result);

            // Recount
            $this->assertEquals($fullCount, $dao->count());
            $this->assertEquals($status1Count, $dao->count([ 'status_id' => 1, ]));
            $this->assertEquals($status2Count, $dao->count([ 'status_id' => 2, ]));
            $this->assertEquals($passwordCost50Count, $dao->count([ 'password_cost' => 50, ]));
            $this->assertEquals($passwordCost55Count, $dao->count([ 'password_cost' => 55, ]));
            $this->assertEquals($status1PasswordCost50Count, $dao->count([ 'status_id' => 1, 'password_cost' => 50, ]));
            $this->assertEquals($status2PasswordCost55Count, $dao->count([ 'status_id' => 2, 'password_cost' => 55, ]));

            $passwordCostMultiCountDeleted = $dao->countMulti([
                [ 'password_cost' => 50, ],
                [ 'password_cost' => 55, ],
            ]);
            $this->assertEquals($passwordCostMultiCountOriginal[0], $passwordCostMultiCountDeleted[0]);
            $this->assertEquals($passwordCostMultiCountOriginal[1], $passwordCostMultiCountDeleted[1]);
        }

        protected function daoLimit(Neoform\Entity\Record\Test\Dao $dao) {

            // Return model
            $models = [];
            $pks    = [];
            for ($i=0; $i < 10; $i++) {
                $email  = sha1(mt_rand()) . '@example.com';
                $result = $dao->insert(
                    [
                        'email'               => $email,
                        'password_hash'       => 'aaa',
                        'password_hashmethod' => 1,
                        'password_cost'       => 50,
                        'password_salt'       => 'bbb',
                        'status_id'           => 1,
                    ],
                    false,
                    true,
                    true
                );
                $this->assertTrue($result instanceof Neoform\User\Model);
                $this->assertTrue((bool) $result->id);
                $this->assertEquals($result->email, $email);
                $this->assertEquals($result->password_hash, 'aaa');
                $this->assertEquals($result->password_hashmethod, 1);
                $this->assertEquals($result->password_cost, 50);
                $this->assertEquals($result->password_salt, 'bbb');
                $this->assertEquals($result->status_id, 1);

                $models[] = $result;
                $pks[]    = $result->id;
            }

            // Desc 0, 5
            $ids = $dao->limit(['id' => Neoform\Entity\Dao::SORT_DESC, ], 0, 5);

            $this->assertTrue(is_array($ids));
            $this->assertEquals(count($ids), 5);

            $this->assertEquals((int) $ids[0], (int) $pks[9]);
            $this->assertEquals((int) $ids[1], (int) $pks[8]);
            $this->assertEquals((int) $ids[2], (int) $pks[7]);
            $this->assertEquals((int) $ids[3], (int) $pks[6]);
            $this->assertEquals((int) $ids[4], (int) $pks[5]);

            // Desc 0, 10
            $ids = $dao->limit(['id' => Neoform\Entity\Dao::SORT_DESC, ], 0, 10);

            $this->assertTrue(is_array($ids));
            $this->assertEquals(count($ids), 10);

            $this->assertEquals((int) $ids[0], (int) $pks[9]);
            $this->assertEquals((int) $ids[1], (int) $pks[8]);
            $this->assertEquals((int) $ids[2], (int) $pks[7]);
            $this->assertEquals((int) $ids[3], (int) $pks[6]);
            $this->assertEquals((int) $ids[4], (int) $pks[5]);
            $this->assertEquals((int) $ids[5], (int) $pks[4]);
            $this->assertEquals((int) $ids[6], (int) $pks[3]);
            $this->assertEquals((int) $ids[7], (int) $pks[2]);
            $this->assertEquals((int) $ids[8], (int) $pks[1]);
            $this->assertEquals((int) $ids[9], (int) $pks[0]);

            // Desc 5, 2
            $ids = $dao->limit(['id' => Neoform\Entity\Dao::SORT_DESC, ], 5, 2);

            $this->assertTrue(is_array($ids));
            $this->assertEquals(count($ids), 2);

            $this->assertEquals((int) $ids[0], (int) $pks[4]);
            $this->assertEquals((int) $ids[1], (int) $pks[3]);
        }

        protected function daoByField(Neoform\Entity\Record\Test\Dao $dao) {

            // Return model
            $models = [];
            $pks    = [];
            for ($i=0; $i < 10; $i++) {
                $email  = sha1(mt_rand()) . '@example.com';
                $result = $dao->insert(
                    [
                        'email'               => $email,
                        'password_hash'       => 'aaa',
                        'password_hashmethod' => 1,
                        'password_cost'       => 50,
                        'password_salt'       => 'bbb',
                        'status_id'           => ($i % 2) + 1,
                    ],
                    false,
                    true,
                    true
                );
                $this->assertTrue($result instanceof Neoform\User\Model);
                $this->assertTrue((bool) $result->id);
                $this->assertEquals($result->email, $email);
                $this->assertEquals($result->password_hash, 'aaa');
                $this->assertEquals($result->password_hashmethod, 1);
                $this->assertEquals($result->password_cost, 50);
                $this->assertEquals($result->password_salt, 'bbb');
                $this->assertEquals($result->status_id, ($i % 2) + 1);

                $models[] = $result;
                $pks[]    = $result->id;
            }

            // status: 0 Desc 0, 5
            $ids = $dao->by_status(0, [ 'id' => Neoform\Entity\Dao::SORT_DESC, ], 0, 5);

            $this->assertTrue(is_array($ids));
            $this->assertEquals(count($ids), 0);

            // status: 1 Desc 0, 5
            $ids = $dao->by_status(1, [ 'id' => Neoform\Entity\Dao::SORT_DESC, ], 0, 5);

            $this->assertTrue(is_array($ids));
            $this->assertEquals(count($ids), 5);

            $this->assertEquals((int) $ids[0], (int) $pks[8]);
            $this->assertEquals((int) $ids[1], (int) $pks[6]);
            $this->assertEquals((int) $ids[2], (int) $pks[4]);
            $this->assertEquals((int) $ids[3], (int) $pks[2]);
            $this->assertEquals((int) $ids[4], (int) $pks[0]);

            // status: 0 Desc 0, 5
            $ids = $dao->by_status(2, [ 'id' => Neoform\Entity\Dao::SORT_DESC, ], 0, 5);

            $this->assertTrue(is_array($ids));
            $this->assertEquals(count($ids), 5);

            $this->assertEquals((int) $ids[0], (int) $pks[9]);
            $this->assertEquals((int) $ids[1], (int) $pks[7]);
            $this->assertEquals((int) $ids[2], (int) $pks[5]);
            $this->assertEquals((int) $ids[3], (int) $pks[3]);
            $this->assertEquals((int) $ids[4], (int) $pks[1]);
        }

        protected function daoByFieldUnique(Neoform\Entity\Record\Test\Dao $dao) {

            $email  = sha1(mt_rand()) . '@example.com';
            $model = $dao->insert(
                [
                    'email'               => $email,
                    'password_hash'       => 'aaa',
                    'password_hashmethod' => 1,
                    'password_cost'       => 50,
                    'password_salt'       => 'bbb',
                    'status_id'           => 1,
                ],
                false,
                true,
                true
            );
            $this->assertTrue($model instanceof Neoform\User\Model);
            $this->assertTrue((bool) $model->id);
            $this->assertEquals($model->email, $email);
            $this->assertEquals($model->password_hash, 'aaa');
            $this->assertEquals($model->password_hashmethod, 1);
            $this->assertEquals($model->password_cost, 50);
            $this->assertEquals($model->password_salt, 'bbb');
            $this->assertEquals($model->status_id, 1);

            // email: bad@email.com
            $ids = $dao->by_email('bad@email.com');

            $this->assertTrue(is_array($ids));
            $this->assertEquals(count($ids), 0);

            // status: 1 Desc 0, 5
            $ids = $dao->by_email($email);

            $this->assertTrue(is_array($ids));
            $this->assertEquals(count($ids), 1);
            $this->assertEquals((int) $ids[0], $model->id);
        }

        protected function daoByFieldMulti(Neoform\Entity\Record\Test\Dao $dao) {

            // Return model
            $pks    = [];
            for ($i=0; $i < 10; $i++) {
                $email  = sha1(mt_rand()) . '@example.com';
                $result = $dao->insert(
                    [
                        'email'               => $email,
                        'password_hash'       => 'aaa',
                        'password_hashmethod' => 1,
                        'password_cost'       => 50,
                        'password_salt'       => 'bbb',
                        'status_id'           => ($i % 2) + 1,
                    ],
                    false,
                    true,
                    true
                );
                $this->assertTrue($result instanceof Neoform\User\Model);
                $this->assertTrue((bool) $result->id);
                $this->assertEquals($result->email, $email);
                $this->assertEquals($result->password_hash, 'aaa');
                $this->assertEquals($result->password_hashmethod, 1);
                $this->assertEquals($result->password_cost, 50);
                $this->assertEquals($result->password_salt, 'bbb');
                $this->assertEquals($result->status_id, ($i % 2) + 1);

                $pks[] = $result->id;
            }

            $idsArr = $dao->by_status_multi(
                [0, 1, 2],
                [ 'id' => Neoform\Entity\Dao::SORT_DESC, ],
                0,
                5
            );

            $this->assertTrue(is_array($idsArr));
            $this->assertEquals(count($idsArr), 3);

            $this->assertTrue(is_array($idsArr[0]));
            $this->assertEquals(count($idsArr[0]), 0);
            $this->assertTrue(is_array($idsArr[1]));
            $this->assertEquals(count($idsArr[1]), 5);
            $this->assertTrue(is_array($idsArr[2]));
            $this->assertEquals(count($idsArr[2]), 5);

            $this->assertEquals((int) $idsArr[1][0], (int) $pks[8]);
            $this->assertEquals((int) $idsArr[1][1], (int) $pks[6]);
            $this->assertEquals((int) $idsArr[1][2], (int) $pks[4]);
            $this->assertEquals((int) $idsArr[1][3], (int) $pks[2]);
            $this->assertEquals((int) $idsArr[1][4], (int) $pks[0]);

            $this->assertEquals((int) $idsArr[2][0], (int) $pks[9]);
            $this->assertEquals((int) $idsArr[2][1], (int) $pks[7]);
            $this->assertEquals((int) $idsArr[2][2], (int) $pks[5]);
            $this->assertEquals((int) $idsArr[2][3], (int) $pks[3]);
            $this->assertEquals((int) $idsArr[2][4], (int) $pks[1]);
        }

        protected function loadInfoNoCache($id) {
            $config = new Neoform\Entity\Config\Overridden([
                'source_engine'             => 'MySQL',
                'source_engine_pool_read'   => 'master',
                'source_engine_pool_write'  => 'master',
                'source_engine_ttl'         => null,
                'cache_engine'              => null,
                'cache_engine_pool_read'    => null,
                'cache_engine_pool_write'   => null,
                'cache_meta_engine'         => null,
                'cache_meta_engine_pool'    => null,
                'cache_delete_expire_ttl'   => null,
                'cache_use_binary_keys'     => true,
            ]);

            $dao = new Neoform\Entity\Record\Test\Dao($config);

            return $dao->record($id);
        }

        protected function loadInfoWithCache($id) {
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

            $dao = new Neoform\Entity\Record\Test\Dao($config);

            return $dao->record($id);
        }
    }