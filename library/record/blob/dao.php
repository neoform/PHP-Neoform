<?php

    /**
     * Record Blob DAO
     */
    class record_blob_dao extends record_dao {

        /**
         * @param string|int $pk Primary key
         *
         * @return array cached record data
         * @throws record_exception
         */
        public static function by_pk($pk) {
            $info = parent::by_pk($pk);
            $info[static::BLOB] = json_decode($info[static::BLOB], true);
            return $info;
        }

        /**
         * Pulls a single record's information from the database
         *
         * @access public
         * @static
         * @param array  $pks primary key of a records
         * @return array cached records data - with preserved key names from $pks.
         */
        public static function by_pks(array $pks) {
            $infos = parent::by_pks($pks);
            foreach ($infos as & $info) {
                $info[static::BLOB] = json_decode($info[static::BLOB], true);
            }
            return $infos;
        }

        /**
         * Inserts a record into the database
         *
         * @access protected
         * @static
         * @param array   $info          an associative array of into to be put into the database
         * @param boolean $replace       optional - user REPLACE INTO instead of INSERT INTO
         * @param boolean $return_model  optional - return a model of the new record
         * @return record_model|true if $return_model is set to true, the model created from the info is returned
         */
        protected static function _insert(array $info, $replace=false, $return_model = true) {
            if (isset($info[static::BLOB]) && is_array($info[static::BLOB])) {
                $info[static::BLOB] = json_encode(type_array_lib::collapse($info[static::BLOB], false));
            } else {
                $info[static::BLOB] = json_encode([]);
            }
            return parent::_insert($info, $replace, $return_model);
        }

        /**
         * Inserts multiple record into the database
         *
         * @access protected
         * @static
         * @param array   $infos             an array of associative arrays of into to be put into the database, if this dao represents multiple tables, the info will be split up across the applicable tables.
         * @param boolean $keys_match        optional - if all the records being inserted have the same array keys this should be true. it is faster to insert all the records at the same time, but this can only be done if they all have the same keys.
         * @param boolean $replace           optional - user REPLACE INTO instead of INSERT INTO
         * @param boolean $return_collection optional - return a collection of models created
         * @return record_collection|true if $return_collection is true function returns a collection
         */
        protected static function _inserts(array $infos, $keys_match = true, $replace=false, $return_collection = true) {
            foreach ($infos as & $info) {
                if (isset($info[static::BLOB]) && is_array($info[static::BLOB])) {
                    $info[static::BLOB] = json_encode(type_array_lib::collapse($info[static::BLOB], false));
                } else {
                    $info[static::BLOB] = json_encode([]);
                }
            }
            return parent::_insert($infos, $keys_match, $replace, $return_collection);
        }

        /**
         * Updates a record in the database
         *
         * @access protected
         * @static
         * @param record_model $model        the model that is to be updated
         * @param array        $info         the new info to be put into the model
         * @param boolean      $return_model optional - return a model of the new record
         * @return record_model|true if $return_model is true, an updated model is returned
         */
        protected static function _update(record_model $model, array $info, $return_model = true) {
            if (isset($info[static::BLOB]) && is_array($info[static::BLOB])) {
                $info[static::BLOB] = json_encode(type_array_lib::collapse($info[static::BLOB], false));
            } else {
                $info[static::BLOB] = json_encode([]);
            }
            return parent::_update($model, $info, $return_model);
        }
    }