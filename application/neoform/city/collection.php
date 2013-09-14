<?php

    namespace neoform\city;

    /**
     * City collection
     */
    class collection extends \neoform\entity\record\collection implements definition {

        /**
         * Preload the Region models in this collection
         *
         * @return \neoform\region\collection
         */
        public function region_collection() {
            return $this->_preload_one_to_one(
                'region',
                'region',
                'region_id'
            );
        }
    }
