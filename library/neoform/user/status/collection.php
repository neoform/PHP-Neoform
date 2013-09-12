<?php

    namespace neoform\user\status;

    /**
     * User Status collection
     */
    class collection extends \neoform\entity\record\collection implements definition {

        /**
         * Preload the User models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity\record_dao::SORT_ASC, entity\record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\user\collection
         */
        public function user_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'user_collection',
                '\neoform\user',
                'by_status',
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
                '\neoform\user',
                'status_id'
            );
        }
    }
