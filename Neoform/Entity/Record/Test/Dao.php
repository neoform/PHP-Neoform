<?php

    namespace Neoform\Entity\Record\Test;

    use Neoform;

    /**
     * User DAO
     */
    class Dao extends \Neoform\Entity\Record\Dao {

        const BY_EMAIL               = 'by_email';
        const BY_PASSWORD_HASHMETHOD = 'by_password_hashmethod';
        const BY_STATUS              = 'by_status';

        const NAME          = 'test user';
        const TABLE         = 'test';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'Neoform\User';
        const CACHE_KEY     = 'testuser';

        /**
         * $var array $fieldBindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $fieldBindings = [
            'id'                  => self::TYPE_INTEGER,
            'email'               => self::TYPE_STRING,
            'password_hash'       => self::TYPE_BINARY,
            'password_hashmethod' => self::TYPE_INTEGER,
            'password_cost'       => self::TYPE_INTEGER,
            'password_salt'       => self::TYPE_BINARY,
            'status_id'           => self::TYPE_INTEGER,
        ];

        /**
         * $var array $referencedEntities list of fields (in this entity) and their related foreign entity
         *
         * @return array
         */
        protected $referencedEntities = [
            'password_hashmethod' => 'Neoform\User\Hashmethod',
            'status_id'           => 'Neoform\User\Status',
        ];

        // READS

        /**
         * Get User ids by password_hashmethod
         *
         * @param int $password_hashmethod
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of User ids
         */
        public function by_password_hashmethod($password_hashmethod, array $order_by=null, $offset=null, $limit=null) {
            return parent::_byFields(
                self::BY_PASSWORD_HASHMETHOD,
                [
                    'password_hashmethod' => (int) $password_hashmethod,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get User ids by status
         *
         * @param int $status_id
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of User ids
         */
        public function by_status($status_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_byFields(
                self::BY_STATUS,
                [
                    'status_id' => (int) $status_id,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get User ids by email
         *
         * @param string $email
         *
         * @return array of User ids
         */
        public function by_email($email) {
            return parent::_byFields(
                self::BY_EMAIL,
                [
                    'email' => (string) $email,
                ]
            );
        }

        /**
         * Get multiple sets of User ids by user_hashmethod
         *
         * @param \Neoform\User\Hashmethod\Collection|array $user_hashmethod_list
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays containing User ids
         */
        public function by_password_hashmethod_multi($user_hashmethod_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($user_hashmethod_list instanceof \Neoform\User\Hashmethod\Collection) {
                foreach ($user_hashmethod_list as $k => $user_hashmethod) {
                    $keys[$k] = [
                        'password_hashmethod' => (int) $user_hashmethod->id,
                    ];
                }
            } else {
                foreach ($user_hashmethod_list as $k => $user_hashmethod) {
                    $keys[$k] = [
                        'password_hashmethod' => (int) $user_hashmethod,
                    ];
                }
            }
            return parent::_byFieldsMulti(
                self::BY_PASSWORD_HASHMETHOD,
                $keys,
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get multiple sets of User ids by user_status
         *
         * @param \Neoform\User\Status\Collection|array $user_status_list
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays containing User ids
         */
        public function by_status_multi($user_status_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($user_status_list instanceof \Neoform\User\Status\Collection) {
                foreach ($user_status_list as $k => $user_status) {
                    $keys[$k] = [
                        'status_id' => (int) $user_status->id,
                    ];
                }
            } else {
                foreach ($user_status_list as $k => $user_status) {
                    $keys[$k] = [
                        'status_id' => (int) $user_status,
                    ];
                }
            }
            return parent::_byFieldsMulti(
                self::BY_STATUS,
                $keys,
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get User id_arr by an array of emails
         *
         * @param array $email_arr an array containing emails
         *
         * @return array of arrays of User ids
         */
        public function by_email_multi(array $email_arr) {
            $keys_arr = [];
            foreach ($email_arr as $k => $email) {
                $keys_arr[$k] = [ 'email' => (string) $email, ];
            }
            return parent::_byFieldsMulti(
                self::BY_EMAIL,
                $keys_arr
            );
        }

        // WRITES

        /**
         * Insert User record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return Neoform\User\Model|bool
         */
        public function insert(array $info, $replace, $returnModel, $loadModelFromSource) {

            // Insert record
            return parent::_insert($info, $replace, $returnModel, $loadModelFromSource);
        }

        /**
         * Insert multiple User records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return Neoform\User\Collection
         */
        public function insertMulti(array $infos, $keysMatch, $replace, $returnCollection, $loadModelsFromSource) {

            // Insert record
            return parent::_insertMulti($infos, $keysMatch, $replace, $returnCollection, $loadModelsFromSource);
        }

        /**
         * Updates a User record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param Neoform\User\Model $user record to be updated
         * @param array $info data to write to the record
         *
         * @return Neoform\User\Model|bool updated model
         */
        public function update(Neoform\User\Model $user, array $info, $returnModel, $loadModelFromSource) {

            // Update record
            return parent::_update($user, $info, $returnModel, $loadModelFromSource);
        }

        /**
         * Delete a User record
         *
         * @param Neoform\User\Model $user record to be deleted
         *
         * @return bool
         */
        public function delete(Neoform\User\Model $user) {

            // Delete record
            return parent::_delete($user);
        }

        /**
         * Delete multiple User records
         *
         * @param Neoform\User\Collection $user_collection records to be deleted
         *
         * @return bool
         */
        public function deleteMulti(Neoform\User\Collection $user_collection) {

            // Delete records
            return parent::_deleteMulti($user_collection);
        }
    }
