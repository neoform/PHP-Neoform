<?php

    /**
     * Region collection
     */
    class region_collection extends entity_record_collection implements region_definition {

        /**
         * Preload the City models in this collection
         *
         * @return city_collection
         */
        public function city_collection() {
            return $this->_preload_one_to_many('city', 'by_region');
        }

        /**
         * Preload the Country models in this collection
         *
         * @return country_collection
         */
        public function country_collection() {
            return $this->_preload_one_to_one('country', 'country_id');
        }
    }
