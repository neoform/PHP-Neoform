<?php

    namespace Neoform\Entity\Record\Blob;

    use Neoform\Entity\Record;
    use Neoform\Entity;
    use Neoform\Type\Arr\Lib as array_lib;

    /**
     * Record Blob DAO
     */
    class Dao extends Record\Dao {

        /**
         * @param string|int $pk Primary key
         *
         * @return array cached record data
         * @throws Entity\Exception
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
         * @param array   $info                   an associative array of into to be put into the database
         * @param boolean $replace                optional - user REPLACE INTO instead of INSERT INTO
         * @param boolean $return_model           optional - return a model of the new record
         * @param boolean $load_model_from_source optional - after insert, load data from source - this is needed if the DB changes values on insert (eg, timestamps)
         *
         * @return model|boolean if $return_model is set to true, the model created from the info is returned
         */
        protected function _insert(array $info, $replace=false, $return_model=true, $load_model_from_source=false) {
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
         * @param array   $infos                    an array of associative arrays of into to be put into the database, if this dao represents multiple tables, the info will be split up across the applicable tables.
         * @param boolean $keys_match               optional - if all the records being inserted have the same array keys this should be true. it is faster to insert all the records at the same time, but this can only be done if they all have the same keys.
         * @param boolean $replace                  optional - user REPLACE INTO instead of INSERT INTO
         * @param boolean $return_collection        optional - return a collection of models created
         * @param boolean $load_models_from_source  optional - after insert, load data from source - this is needed if the DB changes values on insert (eg, timestamps)
         *
         * @return Record\Collection|boolean if $return_collection is true function returns a collection
         * @throws Record\Exception
         */
        protected function _insertMulti(array $infos, $keys_match=true, $replace=false, $return_collection=true,
                                         $load_models_from_source=false) {
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
         * @param Record\Model $model                    the model that is to be updated
         * @param array        $new_info                 the new info to be put into the model
         * @param boolean      $return_model             optional - return a model of the new record
         * @param boolean      $reload_model_from_source optional - after update, load data from source - this is needed if the DB changes values on update (eg, timestamps)
         *
         * @return model|bool if $return_model is true, an updated model is returned
         * @throws Record\Exception
         */
        protected function _update(Record\Model $model, array $new_info, $return_model=true,
                                   $reload_model_from_source=false) {
            if (isset($info[static::BLOB]) && is_array($info[static::BLOB])) {
                $info[static::BLOB] = json_encode(array_lib::collapse($info[static::BLOB], false));
            } else {
                $info[static::BLOB] = json_encode([]);
            }
            return parent::_update($model, $info, $return_model);
        }
    }