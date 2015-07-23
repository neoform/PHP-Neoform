<?php

    namespace Neoform\User\Date;

    /**
     * User Date DAO
     */
    class Dao extends \Neoform\Entity\Record\Dao implements Definition {


        /**
         * $var array $fieldBindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $fieldBindings = [
            'user_id'             => self::TYPE_INTEGER,
            'created_on'          => self::TYPE_STRING,
            'last_login'          => self::TYPE_STRING,
            'email_verified_on'   => self::TYPE_STRING,
            'password_updated_on' => self::TYPE_STRING,
        ];

        /**
         * $var array $referencedEntities list of fields (in this entity) and their related foreign entity
         *
         * @return array
         */
        protected $referencedEntities = [
            'user_id' => 'Neoform\User',
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
            return parent::_insert($info, false, true, true);
        }

        /**
         * Insert multiple User Date records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return collection
         */
        public function insertMulti(array $infos) {

            // Insert record
            return parent::_insertMulti($infos, true, false, true, true);
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
        public function update(Model $user_date, array $info) {

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
        public function delete(Model $user_date) {

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
        public function deleteMulti(Collection $user_date_collection) {

            // Delete records
            return parent::_deleteMulti($user_date_collection);
        }
    }
