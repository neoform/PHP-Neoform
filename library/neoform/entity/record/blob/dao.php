<?php

    namespace neoform\entity\record\blob;

    use neoform\entity\record;
    use neoform\entity;
    use neoform\type\arr\lib as array_lib;

    /**
     * Record Blob DAO
     */
    class dao extends record\dao {

        /**
         * @param string|int $pk Primary key
         *
         * @return array cached record data
         * @throws entity\exception
         */
        public function record($pk) {
            $info = parent::record($pk);
            $info[static::BLOB] = json_decode($info[static::BLOB], true);
            return $info;
        }

        /**
         * Pulls a single record's information from the database
         *
         * @param array  $pks primary key of a records
         *
         * @return array cached records data - with preserved key names from $pks.
         */
        public function records(array $pks) {
            $infos = parent::records($pks);
            foreach ($infos as & $info) {
                $info[static::BLOB] = json_decode($info[static::BLOB], true);
            }
            return $infos;
        }

        /**
         * Inserts a record into the database
         *
         * @param array   $info          an associative array of into to be put into the database
         * @param boolean $replace       optional - user REPLACE INTO instead of INSERT INTO
         * @param boolean $return_model  optional - return a model of the new record
         *
         * @return record\model|bool if $return_model is set to true, the model created from the info is returned
         */
        protected function _insert(array $info, $replace=false, $return_model=true) {
            if (isset($info[static::BLOB]) && is_array($info[static::BLOB])) {
                $info[static::BLOB] = json_encode(array_lib::collapse($info[static::BLOB], false));
            } else {
                $info[static::BLOB] = json_encode([]);
            }
            return parent::_insert($info, $replace, $return_model);
        }

        /**
         * Inserts multiple record into the database
         *
         * @param array   $infos             an array of associative arrays of into to be put into the database, if this dao represents multiple tables, the info will be split up across the applicable tables.
         * @param boolean $keys_match        optional - if all the records being inserted have the same array keys this should be true. it is faster to insert all the records at the same time, but this can only be done if they all have the same keys.
         * @param boolean $replace           optional - user REPLACE INTO instead of INSERT INTO
         * @param boolean $return_collection optional - return a collection of models created
         *
         * @return record\collection|bool if $return_collection is true function returns a collection
         */
        protected function _insert_multi(array $infos, $keys_match = true, $replace=false, $return_collection=true) {
            foreach ($infos as & $info) {
                if (isset($info[static::BLOB]) && is_array($info[static::BLOB])) {
                    $info[static::BLOB] = json_encode(array_lib::collapse($info[static::BLOB], false));
                } else {
                    $info[static::BLOB] = json_encode([]);
                }
            }
            return parent::_insert($infos, $keys_match, $replace, $return_collection);
        }

        /**
         * Updates a record in the database
         *
         * @param record\model $model        the model that is to be updated
         * @param array        $info         the new info to be put into the model
         * @param boolean      $return_model optional - return a model of the new record
         *
         * @return record\model|bool if $return_model is true, an updated model is returned
         */
        protected function _update(record\model $model, array $info, $return_model=true) {
            if (isset($info[static::BLOB]) && is_array($info[static::BLOB])) {
                $info[static::BLOB] = json_encode(array_lib::collapse($info[static::BLOB], false));
            } else {
                $info[static::BLOB] = json_encode([]);
            }
            return parent::_update($model, $info, $return_model);
        }
    }