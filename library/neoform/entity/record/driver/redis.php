<?php

    namespace neoform\entity\record\driver;

    use neoform\entity\record;
    use neoform\entity;
    use neoform\core;

    class redis implements record\driver {

        /**
         * Get full record by primary key
         *
         * @param record\dao $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param integer|string    $pk
         *
         * @return mixed
         * @throws entity\exception
         */
        public static function record(record\dao $self, $pool, $pk) {

            $key = '_db_:' . $self::TABLE . ":{$pk}";

            $redis = core::redis($pool);
            $data  = $redis->get($key);

            // since false is potentially a valid result being stored in redis, we must check if the key exists
            if ($data === false && ! $redis->exists($key)) {
                $exception = '\\neoform\\' . $self::ENTITY_NAME . '\\exception';
                throw new $exception('That ' . $self::NAME . ' doesn\'t exist');
            } else {
                return $data;
            }
        }

        /**
         * Get full records by primary key
         *
         * @param record\dao $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param array             $pks
         *
         * @return array
         */
        public static function records(record\dao $self, $pool, array $pks) {
            $keys = [];
            foreach ($pks as $k => $pk) {
                $keys[$k] = '_db_:' . $self::TABLE . ":{$pk}";
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
         * Get a count based on key inputs
         *
         * @param record\dao $self
         * @param string            $pool
         * @param array             $keys
         *
         * @throws entity\exception
         */
        public static function count(record\dao $self, $pool, array $keys=null) {
            throw new entity\exception('Count queries are not supported by redis driver.');
        }

        /**
         * Get multiple counts
         *
         * @param record\dao $self
         * @param string            $pool
         * @param array             $fieldvals_arr
         *
         * @throws entity\exception
         */
        public static function count_multi(record\dao $self, $pool, array $fieldvals_arr) {
            throw new entity\exception('Count queries are not supported by redis driver.');
        }

        /**
         * Get all records in the table
         *
         * @param record\dao $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param int|string        $pk
         * @param array             $keys
         *
         * @throws entity\exception
         */
        public static function all(record\dao $self, $pool, $pk, array $keys=null) {
            throw new entity\exception('Select all queries are not supported by redis driver.');
        }

        /**
         * Get record primary key by fields
         *
         * @param record\dao $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param array             $keys
         * @param int|string        $pk
         *
         * @throws entity\exception
         */
        public static function by_fields(record\dao $self, $pool, array $keys, $pk) {
            throw new entity\exception('By fields queries are not supported by redis driver.');
        }

        /**
         * Get multiple record primary keys by fields
         *
         * @param record\dao $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param array             $keys_arr
         * @param int|string        $pk
         *
         * @throws entity\exception
         */
        public static function by_fields_multi(record\dao $self, $pool, array $keys_arr, $pk) {
            throw new entity\exception('By fields multi queries are not supported by redis driver.');
        }

        /**
         * Get a set of PKs based on params, in a given order and offset/limit
         *
         * @param record\dao $self
         * @param string            $pool
         * @param array             $keys
         * @param mixed             $pk
         * @param array             $order_by
         * @param integer|null      $offset
         * @param integer           $limit
         *
         * @throws entity\exception
         */
        public static function by_fields_offset(record\dao $self, $pool, array $keys, $pk, array $order_by, $offset, $limit) {
            throw new entity\exception('By fields offset queries are not supported by redis driver.');
        }

        /**
         * Get multiple sets of PKs based on params, in a given order and offset/limit
         *
         * @param record\dao $self
         * @param string            $pool
         * @param array             $keys_arr
         * @param mixed             $pk
         * @param array             $order_by
         * @param integer|null      $offset
         * @param integer           $limit
         *
         * @throws entity\exception
         */
        public static function by_fields_offset_multi(record\dao $self, $pool, array $keys_arr, $pk, array $order_by, $offset, $limit) {
            throw new entity\exception('By fields multi offset queries are not supported by redis driver.');
        }

        /**
         * Insert record
         *
         * @param record\dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param array      $info
         * @param bool       $autoincrement
         * @param bool       $replace
         *
         * @return array
         * @throws entity\exception
         */
        public static function insert(record\dao $self, $pool, array $info, $autoincrement, $replace) {
            if (! core::redis($pool)->set(
                '_db_:' . $self::TABLE . ':' . $info[$self::PRIMARY_KEY],
                $info
            )) {
                $message = core::redis($pool)->getLastError();
                core::redis($pool)->clearLastError();
                throw new entity\exception("Insert failed - {$message}");
            }

            return $info;
        }

        /**
         * Insert multiple records
         *
         * @param record\dao $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param array             $infos
         * @param bool              $keys_match
         * @param bool              $autoincrement
         * @param bool              $replace
         *
         * @return array
         * @throws entity\exception
         */
        public static function insert_multi(record\dao $self, $pool, array $infos, $keys_match, $autoincrement, $replace) {

            $inserts = [];
            foreach ($infos as $info) {
                $inserts['_db_:' . $self::TABLE . ':' . $info[$self::PRIMARY_KEY]] = $info;
            }

            if (! core::redis($pool)->mset($inserts)) {
                $message = core::redis($pool)->getLastError();
                core::redis($pool)->clearLastError();
                throw new entity\exception("Insert multi failed - {$message}");
            }

            return $infos;
        }

        /**
         * Update a record
         *
         * @param record\dao   $self the name of the DAO
         * @param string       $pool which source engine pool to use
         * @param int|string   $pk
         * @param record\model $model
         * @param array        $info
         *
         * @return bool
         * @throws entity\exception
         */
        public static function update(record\dao $self, $pool, $pk, record\model $model, array $info) {
            if (! core::redis($pool)->set(
                '_db_:' . $self::TABLE . ":{$model->$pk}",
                \array_merge($model->export(), $info)
            )) {
                $message = core::redis($pool)->getLastError();
                core::redis($pool)->clearLastError();
                throw new entity\exception("Update failed - {$message}");
            }

            return true;
        }

        /**
         * Delete a record
         *
         * @param record\dao   $self the name of the DAO
         * @param string       $pool which source engine pool to use
         * @param int|string   $pk
         * @param record\model $model
         *
         * @return bool
         * @throws entity\exception
         */
        public static function delete(record\dao $self, $pool, $pk, record\model $model) {
            if (! core::redis($pool)->delete('_db_:' . $self::TABLE . ":{$model->$pk}")) {
                $message = core::redis($pool)->getLastError();
                core::redis($pool)->clearLastError();
                throw new entity\exception("Delete failed - {$message}");
            }

            return true;
        }

        /**
         * Delete multiple records
         *
         * @param record\dao        $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param int|string        $pk
         * @param record\collection $collection
         *
         * @return bool
         * @throws entity\exception
         */
        public static function delete_multi(record\dao $self, $pool, $pk, record\collection $collection) {
            $keys = [];
            foreach ($collection as $model) {
                $keys[] = '_db_:' . $self::TABLE . ":{$model->$pk}";
            }

            if (! core::redis($pool)->delete($keys)) {
                $message = core::redis($pool)->getLastError();
                core::redis($pool)->clearLastError();
                throw new entity\exception("Delete failed - {$message}");
            }

            return true;
        }
    }