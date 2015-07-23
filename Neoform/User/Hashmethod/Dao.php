<?php

    namespace Neoform\User\Hashmethod;

    /**
     * User Hashmethod DAO
     */
    class Dao extends \Neoform\Entity\Record\Dao implements Definition {

        const BY_NAME = 'by_name';

        /**
         * $var array $fieldBindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $fieldBindings = [
            'id'   => self::TYPE_INTEGER,
            'name' => self::TYPE_STRING,
        ];

        /**
         * $var array $referencedEntities list of fields (in this entity) and their related foreign entity
         *
         * @return array
         */
        protected $referencedEntities = [];

        // READS

        /**
         * Get User Hashmethod ids by name
         *
         * @param string $name
         *
         * @return array of User Hashmethod ids
         */
        public function by_name($name) {
            return parent::_byFields(
                self::BY_NAME,
                [
                    'name' => (string) $name,
                ]
            );
        }

        /**
         * Get User Hashmethod id_arr by an array of names
         *
         * @param array $name_arr an array containing names
         *
         * @return array of arrays of User Hashmethod ids
         */
        public function by_name_multi(array $name_arr) {
            $keys_arr = [];
            foreach ($name_arr as $k => $name) {
                $keys_arr[$k] = [ 'name' => (string) $name, ];
            }
            return parent::_byFieldsMulti(
                self::BY_NAME,
                $keys_arr
            );
        }

        // WRITES

        /**
         * Insert User Hashmethod record, created from an array of $info
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
         * Insert multiple User Hashmethod records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return collection
         */
        public function insertMulti(array $infos) {

            // Insert record
            return parent::_insertMulti($infos);
        }

        /**
         * Updates a User Hashmethod record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param model $user_hashmethod record to be updated
         * @param array $info data to write to the record
         *
         * @return model updated model
         */
        public function update(Model $user_hashmethod, array $info) {

            // Update record
            return parent::_update($user_hashmethod, $info);
        }

        /**
         * Delete a User Hashmethod record
         *
         * @param model $user_hashmethod record to be deleted
         *
         * @return bool
         */
        public function delete(Model $user_hashmethod) {

            // Delete record
            return parent::_delete($user_hashmethod);
        }

        /**
         * Delete multiple User Hashmethod records
         *
         * @param collection $user_hashmethod_collection records to be deleted
         *
         * @return bool
         */
        public function deleteMulti(Collection $user_hashmethod_collection) {

            // Delete records
            return parent::_deleteMulti($user_hashmethod_collection);
        }
    }
