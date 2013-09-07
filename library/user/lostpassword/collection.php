<?php

    /**
     * User Lostpassword collection
     */
    class user_lostpassword_collection extends entity_record_collection implements user_lostpassword_definition {

        /**
         * Preload the User models in this collection
         *
         * @return user_collection
         */
        public function user_collection() {
            return $this->_preload_one_to_one(
                'user',
                'user',
                'user_id'
            );
        }
    }
