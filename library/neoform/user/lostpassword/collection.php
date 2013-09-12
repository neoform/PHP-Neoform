<?php

    namespace neoform\user\lostpassword;

    /**
     * User Lostpassword collection
     */
    class collection extends \neoform\entity\record\collection implements definition {

        /**
         * Preload the User models in this collection
         *
         * @return \neoform\user\collection
         */
        public function user_collection() {
            return $this->_preload_one_to_one(
                'user',
                'neoform\user',
                'user_id'
            );
        }
    }
