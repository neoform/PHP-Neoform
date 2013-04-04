<?php

    /**
     * Country collection
     */
    class country_collection extends record_collection implements country_definition {

        /**
         * Preload the Region models in this collection
         *
         * @return region_collection
         */
        public function region_collection() {
            return $this->_preload_one_to_many('region', 'by_country');
        }

    }
