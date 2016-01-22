<?php

    namespace Neoform\Site;

    /**
     * Site collection
     */
    class Collection extends \Neoform\Entity\Record\Collection {

        // Load entity details into the class
        use Details;

        /**
         * Preload the User models in this collection
         *
         * @param array        $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset   get PKs starting at this offset
         * @param integer|null $limit    max number of PKs to return
         *
         * @return \Neoform\User\Collection
         */
        public function user_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'user_collection',
                'Neoform\User\Site',
                'by_site',
                'Neoform\User',
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
                'Neoform\User\Site',
                'site_id'
            );
        }
    }
