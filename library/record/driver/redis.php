<?php

    class record_driver_redis implements record_driver {

        protected static function pool() {
            static $pool;
            if ($pool === null) {
                $pool = core::config()->redis['default_database_server'];
            }
            return $pool;
        }

        /**
         * Get full record by primary key
         *
         * @param string     $self the name of the DAO
         * @param int|string $pk
         *
         * @return mixed
         * @throws record_exception
         */
        public static function by_pk($self, $pk) {

            $key = 'db:' . $self::TABLE . ":$pk";

            $redis = core::redis(self::pool());
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
         * @param string $self the name of the DAO
         * @param array  $pks
         *
         * @return array
         */
        public static function by_pks($self, array $pks) {
            $keys = [];
            foreach ($pks as $k => $pk) {
                $keys[$k] = 'db:' . $self::TABLE . ":$pk";
            }

            $redis = core::redis(self::pool());
            $infos = $redis->mGet($keys);

            foreach ($infos as $k => & $info) {
                // since false is potentially a valid result being stored in redis, we must check if the key exists
                if ($info === false && ! $redis->exists($keys[$k])) {
                    $info = null;
                }
            }

            return $infos;
        }

        /**
         * Get a list of PKs, with a limit, offset and order by
         *
         * @param string     $self
         * @param integer    $limit     max number of PKs to return
         * @param string     $order_by  field name
         * @param string     $direction asc|desc
         * @param string     $after_pk  A PK offset to be used (it's more efficient to use PK offsets than an SQL 'OFFSET')
         *
         * @return array
         * @throws redis_exception
         */
        public static function limit($self, $limit, $order_by, $direction, $after_pk) {
            throw new redis_exception('Limit queries are not supported by redis driver.');
        }

        /**
         * Get full count of rows in a table
         *
         * @param string $self
         *
         * @return int
         * @throws redis_exception
         */
        public static function count($self) {
            throw new redis_exception('Count queries are not supported by redis driver.');
        }

        /**
         * Get all records in the table
         *
         * @param string     $self the name of the DAO
         * @param int|string $pk
         * @param array      $keys
         *
         * @return array
         * @throws redis_exception
         */
        public static function all($self, $pk, array $keys=null) {
            throw new redis_exception('Select all queries are not supported by redis driver.');
        }

        /**
         * Get record primary key by fields
         *
         * @param string     $self the name of the DAO
         * @param array      $keys
         * @param int|string $pk
         *
         * @return array
         * @throws redis_exception
         */
        public static function by_fields($self, array $keys, $pk) {
            throw new redis_exception('By fields queries are not supported by redis driver.');
        }

        /**
         * Get multiple record primary keys by fields
         *
         * @param string     $self the name of the DAO
         * @param array      $keys_arr
         * @param int|string $pk
         *
         * @return array
         * @throws redis_exception
         */
        public static function by_fields_multi($self, array $keys_arr, $pk) {
            throw new redis_exception('By fields multi queries are not supported by redis driver.');
        }

        /**
         * Get specific fields from a record, by keys
         *
         * @param string $self
         * @param array  $select_fields
         * @param array  $keys
         *
         * @return array
         * @throws redis_exception
         */
        public static function by_fields_select($self, array $select_fields, array $keys) {
            throw new redis_exception('By fields select queries are not supported by redis driver.');
        }

        /**
         * Insert record
         *
         * @param string $self the name of the DAO
         * @param array  $info
         * @param bool   $autoincrement
         * @param boo    $replace
         *
         * @return array
         */
        public static function insert($self, array $info, $autoincrement, $replace) {
            core::redis(self::pool())->set(
                'db:' . $self::TABLE . ':' . $info[$self::PRIMARY_KEY],
                $info
            );

            return $info;
        }

        /**
         * Insert multiple records
         *
         * @param string $self the name of the DAO
         * @param array $infos
         * @param bool  $keys_match
         * @param bool  $autoincrement
         * @param bool  $replace
         *
         * @return array
         */
        public static function inserts($self, array $infos, $keys_match, $autoincrement, $replace) {

            $inserts = [];
            foreach ($infos as $info) {
                $inserts['db:' . $self::TABLE . ':' . $info[$self::PRIMARY_KEY]] = $info;
            }

            return $infos;
        }

        /**
         * Update a record
         *
         * @param string       $self the name of the DAO
         * @param int|string   $pk
         * @param record_model $model
         * @param array        $info
         */
        public static function update($self, $pk, record_model $model, array $info) {
            return core::redis(self::pool())->set(
                'db:' . $self::TABLE . ':' . $model->$pk,
                array_merge($model->export(), $info)
            );
        }

        /**
         * Delete a record
         *
         * @param string       $self the name of the DAO
         * @param int|string   $pk
         * @param record_model $model
         */
        public static function delete($self, $pk, record_model $model) {
            return core::redis(self::pool())->delete('db:' . $self::TABLE . ':' . $model->$pk);
        }

        /**
         * Delete multiple records
         *
         * @param string            $self the name of the DAO
         * @param int|string        $pk
         * @param record_collection $collection
         */
        public static function deletes($self, $pk, record_collection $collection) {
            $keys = [];
            foreach ($collection as $model) {
                $keys[] = 'db:' . $self::TABLE . ':' . $model->$pk;
            }

            return core::redis(self::pool())->delete($keys);
        }
    }