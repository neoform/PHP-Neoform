<?php

    /**
     * Auth collection
     */
    class auth_collection extends record_collection implements auth_definition {

        /**
         * Preload the User models in this collection
         *
         * @return user_collection
         */
        public function user_collection() {
            return $this->_preload_one_to_one('user', 'user_id');
        }

    }
