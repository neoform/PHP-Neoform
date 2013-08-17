<?php

    class entity_record_driver_redis implements entity_record_driver {

        /**
         * Get full record by primary key
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param int|string $pk
         *
         * @return mixed
         * @throws model_exception
         */
        public static function by_pk(entity_record_dao $self, $pool, $pk) {

            $key = 'db:' . $self::TABLE . ":{$pk}";

            $redis = core::redis($pool);
            $data  = $redis->get($key);

            // since false is potentially a valid result being stored in redis, we must check if the key exists
            if ($data === false && ! $redis->exists($key)) {
                $exception = $self::ENTITY_NAME . '_exception';
                throw new $exception('That ' . $self::NAME . ' doesn\'t exist');
            } else {
                return $data;
            }
        }

        /**
         * Get full records by primary key
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param array      $pks
         *
         * @return array
         */
        public static function by_pks(entity_record_dao $self, $pool, array $pks) {
            $keys = [];
            foreach ($pks as $k => $pk) {
                $keys[$k] = 'db:' . $self::TABLE . ":{$pk}";
            }

            $redis = core::redis($pool);

            // Redis returns the results in order - if the key doesn't exist, false is returned - this problematic
            // since false might be an actual value being stored... therefore we check if the key exists if false is
            // returned

            $redis->multi();
            foreach ($keys as $key) {
                $redis->exists($key);
                $redis->get($key);
            }

            $infos         = [];
            $redis_results = $redis->exec();
            $i             = 0;
            foreach ($keys as $k => $key) {
                if ($redis_results[$i]) {
                    $infos[$k] = $redis_results[$i + 1];
                }

                $i += 2;
            }

            return $infos;
        }

        /**
         * Get full count of rows in a table
         *
         * @param entity_record_dao $self
         * @param string     $pool which source engine pool to use
         *
         * @return int
         * @throws redis_exception
         */
        public static function count(entity_record_dao $self, $pool) {
            throw new redis_exception('Count queries are not supported by redis driver.');
        }

        /**
         * Get all records in the table
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param int|string $pk
         * @param array      $keys
         *
         * @return array
         * @throws redis_exception
         */
        public static function all(entity_record_dao $self, $pool, $pk, array $keys=null) {
            throw new redis_exception('Select all queries are not supported by redis driver.');
        }

        /**
         * Get record primary key by fields
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param array      $keys
         * @param int|string $pk
         *
         * @return array
         * @throws redis_exception
         */
        public static function by_fields(entity_record_dao $self, $pool, array $keys, $pk) {
            throw new redis_exception('By fields queries are not supported by redis driver.');
        }

        /**
         * Get multiple record primary keys by fields
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param array      $keys_arr
         * @param int|string $pk
         *
         * @return array
         * @throws redis_exception
         */
        public static function by_fields_multi(entity_record_dao $self, $pool, array $keys_arr, $pk) {
            throw new redis_exception('By fields multi queries are not supported by redis driver.');
        }

        /**
         * Insert record
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param array      $info
         * @param bool       $autoincrement
         * @param boo        $replace
         *
         * @return array
         */
        public static function insert(entity_record_dao $self, $pool, array $info, $autoincrement, $replace) {
            core::redis($pool)->set(
                'db:' . $self::TABLE . ':' . $info[$self::PRIMARY_KEY],
                $info
            );

            return $info;
        }

        /**
         * Insert multiple records
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param array      $infos
         * @param bool       $keys_match
         * @param bool       $autoincrement
         * @param bool       $replace
         *
         * @return array
         */
        public static function inserts(entity_record_dao $self, $pool, array $infos, $keys_match, $autoincrement, $replace) {

            $inserts = [];
            foreach ($infos as $info) {
                $inserts['db:' . $self::TABLE . ':' . $info[$self::PRIMARY_KEY]] = $info;
            }

            core::redis($pool)->mset($inserts);

            return $infos;
        }

        /**
         * Update a record
         *
         * @param entity_record_dao   $self the name of the DAO
         * @param string       $pool which source engine pool to use
         * @param int|string   $pk
         * @param entity_record_model $model
         * @param array        $info
         */
        public static function update(entity_record_dao $self, $pool, $pk, entity_record_model $model, array $info) {
            return core::redis($pool)->set(
                'db:' . $self::TABLE . ":{$model->$pk}",
                array_merge($model->export(), $info)
            );
        }

        /**
         * Delete a record
         *
         * @param entity_record_dao   $self the name of the DAO
         * @param string       $pool which source engine pool to use
         * @param int|string   $pk
         * @param entity_record_model $model
         */
        public static function delete(entity_record_dao $self, $pool, $pk, entity_record_model $model) {
            return core::redis($pool)->delete('db:' . $self::TABLE . ":{$model->$pk}");
        }

        /**
         * Delete multiple records
         *
         * @param entity_record_dao        $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param int|string        $pk
         * @param entity_record_collection $collection
         */
        public static function deletes(entity_record_dao $self, $pool, $pk, entity_record_collection $collection) {
            $keys = [];
            foreach ($collection as $model) {
                $keys[] = 'db:' . $self::TABLE . ":{$model->$pk}";
            }

            return core::redis($pool)->delete($keys);
        }
    }