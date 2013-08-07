<?php

    /**
     * User Status collection
     */
    class user_status_collection extends entity_record_collection implements user_status_definition {

        /**
         * Preload the User models in this collection
         *
         * @return user_collection
         */
        public function user_collection() {
            return $this->_preload_one_to_many('user', 'by_status');
        }

    }
