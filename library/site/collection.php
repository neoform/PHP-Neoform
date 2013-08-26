<?php

    /**
     * Site collection
     */
    class site_collection extends entity_record_collection implements site_definition {

        /**
         * Preload the User models in this collection
         *
         * @return user_collection
         */
        public function user_collection() {
            return $this->_preload_many_to_many(
                'user_site',
                'by_site',
                'user',
                'user_collection'
            );
        }
    }
