<?php

    /**
     * Permission collection
     */
    class permission_collection extends record_collection implements permission_definition {

        /**
         * Preload the User models in this collection
         *
         * @return user_collection
         */
        public function user_collection() {
            return $this->_preload_many_to_many('user_permission', 'by_permission', 'user');
        }

        /**
         * Checks if a given permission is in this collection
         *
         * @param $name name of permission
         *
         * @return bool
         */
        public function allowed($name) {
			return array_search($name, $this->field('name')) !== false;
		}
	}
