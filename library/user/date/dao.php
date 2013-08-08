<?php

    /**
     * User Date DAO
     */
    class user_date_dao extends entity_record_dao implements user_date_definition {

        /**
         * @var array $pdo_bindings list of fields and their corresponding PDO bindings
         */
        protected $pdo_bindings = [
            'user_id'             => PDO::PARAM_INT,
            'created_on'          => PDO::PARAM_STR,
            'last_login'          => PDO::PARAM_STR,
            'email_verified_on'   => PDO::PARAM_STR,
            'password_updated_on' => PDO::PARAM_STR,
        ];

        // WRITES

        /**
         * Insert User Date record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return user_date_model
         */
        public function insert(array $info) {

            // Insert record
            return parent::_insert($info);
        }

        /**
         * Insert multiple User Date records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return user_date_collection
         */
        public function inserts(array $infos) {

            // Insert record
            return parent::_inserts($infos);
        }

        /**
         * Updates a User Date record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param user_date_model $user_date record to be updated
         * @param array $info data to write to the record
         *
         * @return user_date_model updated model
         */
        public function update(user_date_model $user_date, array $info) {

            // Update record
            return parent::_update($user_date, $info);
        }

        /**
         * Delete a User Date record
         *
         * @param user_date_model $user_date record to be deleted
         *
         * @return bool
         */
        public function delete(user_date_model $user_date) {

            // Delete record
            return parent::_delete($user_date);
        }

        /**
         * Delete multiple User Date records
         *
         * @param user_date_collection $user_date_collection records to be deleted
         *
         * @return bool
         */
        public function deletes(user_date_collection $user_date_collection) {

            // Delete records
            return parent::_deletes($user_date_collection);
        }
    }
