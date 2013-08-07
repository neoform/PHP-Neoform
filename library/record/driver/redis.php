<?php

    class record_driver_redis implements record_driver {

        /**
         * Get full record by primary key
         *
         * @param record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param int|string $pk
         *
         * @return mixed
         * @throws model_exception
         */
        public static function by_pk(record_dao $self, $pool, $pk) {

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
         * @param record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param array      $pks
         *
         * @return array
         */
        public static function by_pks(record_dao $self, $pool, array $pks) {
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
         * Get a list of PKs, with a limit, offset and order by
         *
         * @param record_dao $self
         * @param string     $pool which source engine pool to use
         * @param integer    $limit     max number of PKs to return
         * @param string     $order_by  field name
         * @param string     $direction asc|desc
         * @param string     $after_pk  A PK offset to be used (it's more efficient to use PK offsets than an SQL 'OFFSET')
         *
         * @return array
         * @throws redis_exception
         */
        public static function limit(record_dao $self, $pool, $limit, $order_by, $direction, $after_pk) {
            throw new redis_exception('Limit queries are not supported by redis driver.');
        }

        /**
         * Get a paginated list of entity PKs
         *
         * @param record_dao $self
         * @param string     $pool which source engine pool to use
         * @param string     $order_by
         * @param string     $direction
         * @param integer    $offset
         * @param integer    $limit
         *
         * @return array
         * @throws redis_exception
         */
        public static function paginated(record_dao $self, $pool, $order_by, $direction, $offset, $limit) {
            throw new redis_exception('Paginated queries are not supported by redis driver.');
        }

        /**
         * Get full count of rows in a table
         *
         * @param record_dao $self
         * @param string     $pool which source engine pool to use
         *
         * @return int
         * @throws redis_exception
         */
        public static function count(record_dao $self, $pool) {
            throw new redis_exception('Count queries are not supported by redis driver.');
        }

        /**
         * Get all records in the table
         *
         * @param record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param int|string $pk
         * @param array      $keys
         *
         * @return array
         * @throws redis_exception
         */
        public static function all(record_dao $self, $pool, $pk, array $keys=null) {
            throw new redis_exception('Select all queries are not supported by redis driver.');
        }

        /**
         * Get record primary key by fields
         *
         * @param record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param array      $keys
         * @param int|string $pk
         *
         * @return array
         * @throws redis_exception
         */
        public static function by_fields(record_dao $self, $pool, array $keys, $pk) {
            throw new redis_exception('By fields queries are not supported by redis driver.');
        }

        /**
         * Get multiple record primary keys by fields
         *
         * @param record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param array      $keys_arr
         * @param int|string $pk
         *
         * @return array
         * @throws redis_exception
         */
        public static function by_fields_multi(record_dao $self, $pool, array $keys_arr, $pk) {
            throw new redis_exception('By fields multi queries are not supported by redis driver.');
        }

        /**
         * Get specific fields from a record, by keys
         *
         * @param record_dao $self
         * @param string     $pool which source engine pool to use
         * @param array      $select_fields
         * @param array      $keys
         *
         * @return array
         * @throws redis_exception
         */
        public static function by_fields_select(record_dao $self, $pool, array $select_fields, array $keys) {
            throw new redis_exception('By fields select queries are not supported by redis driver.');
        }

        /**
         * Insert record
         *
         * @param record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param array      $info
         * @param bool       $autoincrement
         * @param boo        $replace
         *
         * @return array
         */
        public static function insert(record_dao $self, $pool, array $info, $autoincrement, $replace) {
            core::redis($pool)->set(
                'db:' . $self::TABLE . ':' . $info[$self::PRIMARY_KEY],
                $info
            );

            return $info;
        }

        /**
         * Insert multiple records
         *
         * @param record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param array      $infos
         * @param bool       $keys_match
         * @param bool       $autoincrement
         * @param bool       $replace
         *
         * @return array
         */
        public static function inserts(record_dao $self, $pool, array $infos, $keys_match, $autoincrement, $replace) {

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
         * @param record_dao   $self the name of the DAO
         * @param string       $pool which source engine pool to use
         * @param int|string   $pk
         * @param record_model $model
         * @param array        $info
         */
        public static function update(record_dao $self, $pool, $pk, record_model $model, array $info) {
            return core::redis($pool)->set(
                'db:' . $self::TABLE . ":{$model->$pk}",
                array_merge($model->export(), $info)
            );
        }

        /**
         * Delete a record
         *
         * @param record_dao   $self the name of the DAO
         * @param string       $pool which source engine pool to use
         * @param int|string   $pk
         * @param record_model $model
         */
        public static function delete(record_dao $self, $pool, $pk, record_model $model) {
            return core::redis($pool)->delete('db:' . $self::TABLE . ":{$model->$pk}");
        }

        /**
         * Delete multiple records
         *
         * @param record_dao        $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param int|string        $pk
         * @param record_collection $collection
         */
        public static function deletes(record_dao $self, $pool, $pk, record_collection $collection) {
            $keys = [];
            foreach ($collection as $model) {
                $keys[] = 'db:' . $self::TABLE . ":{$model->$pk}";
            }

            return core::redis($pool)->delete($keys);
        }
    }