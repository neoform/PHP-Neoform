<?php

    namespace Neoform\User\Lostpassword;

    /**
     * User Lostpassword DAO
     */
    class Dao extends \Neoform\Entity\Record\Dao {

        // Load entity details into the class
        use Details;

        const BY_USER = 'by_user';

        /**
         * $var array $fieldBindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $fieldBindings = [
            'hash'      => self::TYPE_STRING,
            'user_id'   => self::TYPE_INTEGER,
            'posted_on' => self::TYPE_STRING,
        ];

        /**
         * $var array $referencedEntities list of fields (in this entity) and their related foreign entity
         *
         * @return array
         */
        protected $referencedEntities = [
            'user_id' => 'Neoform\User',
        ];

        // READS

        /**
         * Get User Lostpassword hashs by user
         *
         * @param int $user_id
         *
         * @return array of User Lostpassword hashs
         */
        public function by_user($user_id) {
            return parent::_byFields(
                self::BY_USER,
                [
                    'user_id' => (int) $user_id,
                ]
            );
        }

        /**
         * Get multiple sets of User Lostpassword hashs by user
         *
         * @param \Neoform\User\Collection|array $user_list
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays containing User Lostpassword hashs
         */
        public function by_user_multi($user_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($user_list instanceof \Neoform\User\Collection) {
                foreach ($user_list as $k => $user) {
                    $keys[$k] = [
                        'user_id' => (int) $user->id,
                    ];
                }
            } else {
                foreach ($user_list as $k => $user) {
                    $keys[$k] = [
                        'user_id' => (int) $user,
                    ];
                }
            }
            return parent::_byFieldsMulti(
                self::BY_USER,
                $keys,
                $order_by,
                $offset,
                $limit
            );
        }

        // WRITES

        /**
         * Insert User Lostpassword record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return Model
         */
        public function insert(array $info) {

            // Insert record
            return parent::_insert($info);
        }

        /**
         * Insert multiple User Lostpassword records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return Collection
         */
        public function insertMulti(array $infos) {

            // Insert record
            return parent::_insertMulti($infos);
        }

        /**
         * Updates a User Lostpassword record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param Model $user_lostpassword record to be updated
         * @param array $info data to write to the record
         *
         * @return Model updated model
         */
        public function update(Model $user_lostpassword, array $info) {

            // Update record
            return parent::_update($user_lostpassword, $info);
        }

        /**
         * Delete a User Lostpassword record
         *
         * @param Model $user_lostpassword record to be deleted
         *
         * @return bool
         */
        public function delete(Model $user_lostpassword) {

            // Delete record
            return parent::_delete($user_lostpassword);
        }

        /**
         * Delete multiple User Lostpassword records
         *
         * @param Collection $user_lostpassword_collection records to be deleted
         *
         * @return bool
         */
        public function deleteMulti(Collection $user_lostpassword_collection) {

            // Delete records
            return parent::_deleteMulti($user_lostpassword_collection);
        }
    }
