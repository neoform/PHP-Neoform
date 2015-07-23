<?php

    namespace Neoform\Entity\Repo\LinkSource;

    use Neoform\Entity;
    use Neoform\Sql;
    use Neoform;

    interface Driver {

        /**
         * Get specific fields from a record, by keys
         *
         * @param array $selectFields
         * @param array $fieldVals
         *
         * @return array
         */
        public function byFields(array $selectFields, array $fieldVals);

        /**
         * Get specific fields from multiple records, by keys
         *
         * @param array $selectFields
         * @param array $fieldvalsArr
         *
         * @return array
         */
        public function byFieldsMulti(array $selectFields, array $fieldvalsArr);

        /**
         * Get specific fields from a record, by keys - joined to its related foreign table - and limited
         *
         * @param string            $localField
         * @param Entity\Record\Dao $foreignDao
         * @param array             $fieldVals
         * @param array             $orderBy
         * @param integer           $limit
         * @param integer           $offset
         *
         * @return array
         */
        public function byFieldsLimit($localField, Entity\Record\Dao $foreignDao, array $fieldVals, array $orderBy, $offset, $limit);

        /**
         * Get specific fields from a record, by keys - joined to its related foreign table - and limited
         *
         * @param string            $localField
         * @param Entity\Record\Dao $foreignDao
         * @param array             $fieldvalsArr
         * @param array             $orderBy
         * @param integer           $limit
         * @param integer           $offset
         *
         * @return array
         */
        public function byFieldsLimitMulti($localField, Entity\Record\Dao $foreignDao, array $fieldvalsArr, array $orderBy, $offset, $limit);

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
         * @param array $fieldvalsArr
         *
         * @return int[]
         */
        public function countMulti(array $fieldvalsArr);

        /**
         * Insert a link
         *
         * @param array $info
         * @param bool  $replace
         *
         * @return array
         * @throws Neoform\Entity\Repo\Exception
         */
        public function insert(array $info, $replace);

        /**
         * Insert multiple links
         *
         * @param array    $infos
         * @param bool     $replace
         *
         * @return array
         * @throws Neoform\Entity\Repo\Exception
         */
        public function insertMulti(array $infos, $replace);

        /**
         * Update a set of links
         *
         * @param array $new_info
         * @param array $where
         *
         * @throws Neoform\Entity\Repo\Exception
         */
        public function update(array $new_info, array $where);

        /**
         * Delete one or more links
         *
         * @param array $fieldVals
         *
         * @throws Neoform\Entity\Repo\Exception
         */
        public function delete(array $fieldVals);

        /**
         * Delete sets of links
         *
         * @param array $fieldvalsArr
         *
         * @throws Neoform\Entity\Repo\Exception
         */
        public function deleteMulti(array $fieldvalsArr);
    }