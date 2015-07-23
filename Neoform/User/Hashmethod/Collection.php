<?php

    namespace Neoform\User\Hashmethod;

    /**
     * User Hashmethod collection
     */
    class Collection extends \Neoform\Entity\Record\Collection implements Definition {

        /**
         * Preload the User models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (Entity\Record_dao::SORT_ASC, Entity\Record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \Neoform\User\Collection
         */
        public function user_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'user_collection',
                'Neoform\User',
                'by_password_hashmethod',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the User counts
         *
         * @return array counts
         */
        public function user_count() {
            return $this->_preload_counts(
                'user_count',
                'Neoform\User',
                'password_hashmethod'
            );
        }
    }
