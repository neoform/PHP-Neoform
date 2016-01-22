<?php

    namespace Neoform\User\Date;

    /**
     * User Date collection
     */
    class Collection extends \Neoform\Entity\Record\Collection {

        // Load entity details into the class
        use Details;

        /**
         * Preload the User models in this collection
         *
         * @return \Neoform\User\Collection
         */
        public function user_collection() {
            return $this->_preload_one_to_one(
                'user',
                'Neoform\User',
                'user_id'
            );
        }
    }
