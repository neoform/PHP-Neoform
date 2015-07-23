<?php

    namespace Neoform\Entity\Repo\RecordSource;

    use Neoform\Entity;
    use Neoform\Sql;
    use Neoform;

    interface Driver {

        /**
         * Get full record by primary key
         *
         * @param int|string|null $pk
         *
         * @return array
         */
        public function record($pk);

        /**
         * Get full records by primary key
         *
         * @param array $pks
         *
         * @return array
         */
        public function records(array $pks);

        /**
         * Get a count
         *
         * @param array $fieldVals
         *
         * @return int
         */
        public function count(array $fieldVals=null);

        /**
         * Get multiple counts
         *
         * @param array $fieldValsArr
         *
         * @return int[]
         */
        public function countMulti(array $fieldValsArr);

        /**
         * Get all records in the table
         *
         * @param array $fieldVals
         *
         * @return array
         */
        public function all(array $fieldVals=null);

        /**
         * Get record primary key by fields
         *
         * @param array $fieldVals
         *
         * @return array
         */
        public function byFields(array $fieldVals);

        /**
         * Get multiple record primary keys by fields
         *
         * @param array $fieldValsArr
         *
         * @return array
         */
        public function byFieldsMulti(array $fieldValsArr);

        /**
         * Get a set of PKs based on params, in a given order and offset/limit
         *
         * @param array        $fieldVals
         * @param array        $orderBy
         * @param integer|null $offset
         * @param integer      $limit
         *
         * @return mixed
         */
        public function byFieldsOffset(array $fieldVals, array $orderBy, $offset, $limit);

        /**
         * Get multiple sets of PKs based on params, in a given order and offset/limit
         *
         * @param array        $fieldValsArr
         * @param array        $orderBy
         * @param integer|null $offset
         * @param integer      $limit
         *
         * @return array
         */
        public function byFieldsOffsetMulti(array $fieldValsArr, array $orderBy, $offset, $limit);

        /**
         * Insert record
         *
         * @param array $info
         * @param bool  $replace
         * @param int   $ttl
         * @param bool  $reloadFromSource
         *
         * @return array
         * @throws Neoform\Entity\Repo\Exception
         */
        public function insert(array $info, $replace, $ttl, $reloadFromSource);

        /**
         * Insert multiple records
         *
         * @param array $infos
         * @param bool  $keyMatch
         * @param bool  $replace
         * @param int   $ttl
         * @param bool  $reloadFromSource
         *
         * @return array
         * @throws Neoform\Entity\Repo\Exception
         */
        public function insertMulti(array $infos, $keyMatch, $replace, $ttl, $reloadFromSource);

        /**
         * Update a record
         *
         * @param Entity\Record\Model $model
         * @param array               $info
         * @param int                 $ttl
         * @param bool                $reloadFromSource
         *
         * @return array|bool
         * @throws Neoform\Entity\Repo\Exception
         */
        public function update(Entity\Record\Model $model, array $info, $ttl, $reloadFromSource);

        /**
         * Delete a record
         *
         * @param Entity\Record\Model $model
         *
         * @throws Neoform\Entity\Repo\Exception
         */
        public function delete(Entity\Record\Model $model);

        /**
         * Delete multiple records
         *
         * @param Entity\Record\Collection $collection
         *
         * @throws Neoform\Entity\Repo\Exception
         */
        public function deleteMulti(Entity\Record\Collection $collection);
    }