<?php

    namespace neoform\user;

    /**
     * User DAO
     */
    class dao extends \neoform\entity\record\dao implements definition {

        const BY_EMAIL               = 'by_email';
        const BY_PASSWORD_HASHMETHOD = 'by_password_hashmethod';
        const BY_STATUS              = 'by_status';

        /**
         * $var array $field_bindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $field_bindings = [
            'id'                  => self::TYPE_INTEGER,
            'email'               => self::TYPE_STRING,
            'password_hash'       => self::TYPE_BINARY,
            'password_hashmethod' => self::TYPE_INTEGER,
            'password_cost'       => self::TYPE_INTEGER,
            'password_salt'       => self::TYPE_BINARY,
            'status_id'           => self::TYPE_INTEGER,
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
            return parent::_by_fields(
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
            return parent::_by_fields(
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
            return parent::_by_fields(
                self::BY_EMAIL,
                [
                    'email' => (string) $email,
                ]
            );
        }

        /**
         * Get multiple sets of User ids by user_hashmethod
         *
         * @param \neoform\user\hashmethod\collection|array $user_hashmethod_list
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays containing User ids
         */
        public function by_password_hashmethod_multi($user_hashmethod_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($user_hashmethod_list instanceof \neoform\user\hashmethod\collection) {
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
            return parent::_by_fields_multi(
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
         * @param \neoform\user\status\collection|array $user_status_list
         * @param array $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return array of arrays containing User ids
         */
        public function by_status_multi($user_status_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($user_status_list instanceof \neoform\user\status\collection) {
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
            return parent::_by_fields_multi(
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
            return parent::_by_fields_multi(
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
         * @return model
         */
        public function insert(array $info) {

            // Insert record
            return parent::_insert($info);
        }

        /**
         * Insert multiple User records, created from an array of arrays of $info
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
         * Updates a User record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param model $user record to be updated
         * @param array $info data to write to the record
         *
         * @return model updated model
         */
        public function update(model $user, array $info) {

            // Update record
            return parent::_update($user, $info);
        }

        /**
         * Delete a User record
         *
         * @param model $user record to be deleted
         *
         * @return bool
         */
        public function delete(model $user) {

            // Delete record
            return parent::_delete($user);
        }

        /**
         * Delete multiple User records
         *
         * @param collection $user_collection records to be deleted
         *
         * @return bool
         */
        public function delete_multi(collection $user_collection) {

            // Delete records
            return parent::_delete_multi($user_collection);
        }
    }
