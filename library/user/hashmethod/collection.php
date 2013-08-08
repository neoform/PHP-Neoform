<?php

    /**
     * User Hashmethod collection
     */
    class user_hashmethod_collection extends entity_record_collection implements user_hashmethod_definition {

        /**
         * Preload the User models in this collection
         *
         * @return user_collection
         */
        public function user_collection() {
            return $this->_preload_one_to_many('user', 'by_password_hashmethod');
        }

    }
