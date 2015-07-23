<?php

    namespace Neoform\User\Lostpassword;

    /**
     * User Lostpassword collection
     */
    class Collection extends \Neoform\Entity\Record\Collection implements Definition {

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
