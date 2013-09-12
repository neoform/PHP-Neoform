<?php

    namespace neoform\user\date;

    /**
     * User Date DAO
     */
    class dao extends \neoform\entity\record\dao implements definition {


        /**
         * $var array $field_bindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $field_bindings = [
            'user_id'             => self::TYPE_INTEGER,
            'created_on'          => self::TYPE_STRING,
            'last_login'          => self::TYPE_STRING,
            'email_verified_on'   => self::TYPE_STRING,
            'password_updated_on' => self::TYPE_STRING,
        ];

        // WRITES

        /**
         * Insert User Date record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return model
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
         * @return collection
         */
        public function insert_multi(array $infos) {

            // Insert record
            return parent::_insert_multi($infos);
        }

        /**
         * Updates a User Date record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param model $user_date record to be updated
         * @param array $info data to write to the record
         *
         * @return model updated model
         */
        public function update(model $user_date, array $info) {

            // Update record
            return parent::_update($user_date, $info);
        }

        /**
         * Delete a User Date record
         *
         * @param model $user_date record to be deleted
         *
         * @return bool
         */
        public function delete(model $user_date) {

            // Delete record
            return parent::_delete($user_date);
        }

        /**
         * Delete multiple User Date records
         *
         * @param collection $user_date_collection records to be deleted
         *
         * @return bool
         */
        public function delete_multi(collection $user_date_collection) {

            // Delete records
            return parent::_delete_multi($user_date_collection);
        }
    }
