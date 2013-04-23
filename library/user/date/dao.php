<?php

    /**
     * User Date DAO
     */
    class user_date_dao extends record_dao implements user_date_definition {

        public static function bindings() {
            return [
                'user_id'             => 'int',
                'created_on'          => 'string',
                'last_login'          => 'string',
                'email_verified_on'   => 'string',
                'password_updated_on' => 'string',
            ];
        }

        // WRITES

        /**
         * Insert User Date record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return user_date_model
         */
        public static function insert(array $info) {
            return parent::_insert($info);
        }

        /**
         * Insert multiple User Date records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return user_date_collection
         */
        public static function inserts(array $infos) {
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
        public static function update(user_date_model $user_date, array $info) {
            return parent::_update($user_date, $info);
        }

        /**
         * Delete a User Date record
         *
         * @param user_date_model $user_date record to be deleted
         *
         * @return bool
         */
        public static function delete(user_date_model $user_date) {
            return parent::_delete($user_date);
        }

        /**
         * Delete multiple User Date records
         *
         * @param user_date_collection $user_date_collection records to be deleted
         *
         * @return bool
         */
        public static function deletes(user_date_collection $user_date_collection) {
            return parent::_deletes($user_date_collection);
        }
    }
