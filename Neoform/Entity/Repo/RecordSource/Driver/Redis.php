<?php

    namespace Neoform\Entity\Repo\RecordSource\Driver;

    use Neoform\Entity\Repo\Exception;
    use Neoform\Entity\Record;
    use Neoform\Entity;
    use Neoform;

    class Redis implements Neoform\Entity\Repo\RecordSource\Driver {

        /**
         * @var Neoform\Redis
         */
        protected $redisServiceRead;

        /**
         * @var Neoform\Redis
         */
        protected $redisServiceWrite;

        /**
         * @var string
         */
        protected $tableName;

        /**
         * @var string
         */
        protected $primaryKey;

        /**
         * @param Entity\Dao    $dao
         * @param Neoform\Redis $redisServiceRead
         * @param Neoform\Redis $redisServiceWrite
         */
        public function __construct(Neoform\Entity\Dao $dao, Neoform\Redis $redisServiceRead, Neoform\Redis $redisServiceWrite) {
            $this->redisServiceRead  = $redisServiceRead;
            $this->redisServiceWrite = $redisServiceWrite;

            $this->primaryKey = $dao::PRIMARY_KEY;
            $this->tableName  = $dao::TABLE;
        }

        /**
         * Get full record by primary key
         *
         * @param integer|string $pk
         *
         * @return array|null
         */
        public function record($pk) {

            $key = "_db_:{$this->tableName}:{$pk}";

            $redis = $this->redisServiceRead->get();
            $data  = $redis->get($key);

            // since false is potentially a valid result being stored in redis, we must check if the key exists
            if ($data === false && ! $redis->exists($key)) {
                //$exception = '\\Neoform\\' . $self::ENTITY_NAME . '\\Exception';
                //throw new $exception('That ' . $self::NAME . ' doesn\'t exist');
            } else {
                return $data;
            }
        }

        /**
         * Get full records by primary key
         *
         * @param array $pks
         *
         * @return array
         */
        public function records(array $pks) {
            $keys = [];
            foreach ($pks as $k => $pk) {
                $keys[$k] = "_db_:{$this->tableName}:{$pk}";
            }

            $redis = $this->redisServiceRead->get();

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
         * @param array $keys
         *
         * @return int
         * @throws Exception
         */
        public function count(array $keys=null) {
            throw new Exception('Count queries are not supported by redis driver.');
        }

        /**
         * Get multiple counts
         *
         * @param array $fieldvals_arr
         *
         * @return int[]
         * @throws Exception
         */
        public function countMulti(array $fieldvals_arr) {
            throw new Exception('Count queries are not supported by redis driver.');
        }

        /**
         * Get all records in the table
         *
         * @param array $keys
         *
         * @return array
         * @throws Exception
         */
        public function all(array $keys=null) {
            throw new Exception('Select all queries are not supported by redis driver.');
        }

        /**
         * Get record primary key by fields
         *
         * @param array $fieldVals
         *
         * @return array
         * @throws Exception
         */
        public function byFields(array $fieldVals) {
            throw new Exception('By fields queries are not supported by redis driver.');
        }

        /**
         * Get multiple record primary keys by fields
         *
         * @param array $fieldValsArr
         *
         * @return array
         * @throws Exception
         */
        public function byFieldsMulti(array $fieldValsArr) {
            throw new Exception('By fields multi queries are not supported by redis driver.');
        }

        /**
         * Get a set of PKs based on params, in a given order and offset/limit
         *
         * @param array        $keys
         * @param array        $orderBy
         * @param integer|null $offset
         * @param integer      $limit
         *
         * @return array
         * @throws Exception
         */
        public function byFieldsOffset(array $keys, array $orderBy, $offset, $limit) {
            throw new Exception('By fields offset queries are not supported by redis driver.');
        }

        /**
         * Get multiple sets of PKs based on params, in a given order and offset/limit
         *
         * @param array        $keysArr
         * @param array        $orderBy
         * @param integer|null $offset
         * @param integer      $limit
         *
         * @return array
         * @throws Exception
         */
        public function byFieldsOffsetMulti(array $keysArr, array $orderBy, $offset, $limit) {
            throw new Exception('By fields multi offset queries are not supported by redis driver.');
        }

        /**
         * Insert record
         *
         * @param array $info
         * @param bool  $replace
         * @param int   $ttl
         * @param bool  $reloadFromSource - ignored since it's useless on redis since nothing changes after insert
         *
         * @return array
         * @throws Exception
         */
        public function insert(array $info, $replace, $ttl, $reloadFromSource) {
            $redis    = $this->redisServiceWrite->get();
            $cacheKey = "_db_:{$this->tableName}:{$info[$this->primaryKey]}";

            if (! $redis->set(
                $cacheKey,
                $info
            )) {
                $message = $redis->getLastError();
                $redis->clearLastError();
                throw new Exception("Insert failed - {$message}");
            }

            if ($ttl) {
                $redis->expire($cacheKey, $ttl);
            }

            return $info;
        }

        /**
         * Insert multiple records
         *
         * @param array $infos
         * @param bool  $keysMatch
         * @param bool  $replace
         * @param int   $ttl
         * @param bool  $reloadFromSource - ignored since it's useless on redis since nothing changes after insert
         *
         * @return array
         * @throws Exception
         */
        public function insertMulti(array $infos, $keysMatch, $replace, $ttl, $reloadFromSource) {

            $inserts = [];
            foreach ($infos as $info) {
                $inserts["_db_:{$this->tableName}:{$info[$this->primaryKey]}"] = $info;
            }

            $redis = $this->redisServiceWrite->get();

            if (! $redis->mset($inserts)) {
                $message = $redis->getLastError();
                $redis->clearLastError();
                throw new Exception("Insert multi failed - {$message}");
            }

            if ($ttl) {
                $redis->multi();
                foreach ($inserts as $key => $info) {
                    $redis->expire($key, $ttl);
                }
                $redis->exec();
            }

            return $infos;
        }

        /**
         * Update a record
         *
         * @param Record\Model $model
         * @param array        $info
         * @param int          $ttl
         * @param bool         $reloadFromSource
         *
         * @return array|bool
         * @throws Exception
         */
        public function update(Record\Model $model, array $info, $ttl, $reloadFromSource) {
            $redis     = $this->redisServiceWrite->get();
            $cacheKey = "_db_:{$this->tableName}:{$model->{$this->primaryKey}}";

            if (! $redis->set(
                $cacheKey,
                array_merge($model->export(), $info)
            )) {
                $message = $redis->getLastError();
                $redis->clearLastError();
                throw new Exception("Update failed - {$message}");
            }

            if ($ttl) {
                $redis->expire($cacheKey, $ttl);
            }

            // Whatever we just set is the full record, no need to actually reload from source
            if ($reloadFromSource) {
                return array_merge($model->export(), $info);
            }

            return true;
        }

        /**
         * Delete a record
         *
         * @param Record\Model $model
         *
         * @return bool
         * @throws Exception
         */
        public function delete(Record\Model $model) {
            if (! $this->redisServiceWrite->get()->delete("_db_:{$this->tableName}:{$model->{$this->primaryKey}}")) {
                $message = $this->redisServiceWrite->get()->getLastError();
                $this->redisServiceWrite->get()->clearLastError();
                throw new Exception("Delete failed - {$message}");
            }

            return true;
        }

        /**
         * Delete multiple records
         *
         * @param Record\Collection $collection
         *
         * @return bool
         * @throws Exception
         */
        public function deleteMulti(Record\Collection $collection) {
            $keys = [];
            foreach ($collection as $model) {
                $keys[] = "_db_:{$this->tableName}:{$model->{$this->primaryKey}}";
            }

            if (! $this->redisServiceWrite->get()->delete($keys)) {
                $message = $this->redisServiceWrite->get()->getLastError();
                $this->redisServiceWrite->get()->clearLastError();
                throw new Exception("Delete failed - {$message}");
            }

            return true;
        }
    }