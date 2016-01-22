<?php

    namespace Neoform\Locale\Nspace;

    /**
     * Locale Namespace DAO
     */
    class Dao extends \Neoform\Entity\Record\Dao {

        // Load entity details into the class
        use Details;

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
         * Get Locale Namespace ids by name
         *
         * @param string $name
         *
         * @return array of Locale Namespace ids
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
         * Get Locale Namespace id_arr by an array of names
         *
         * @param array $name_arr an array containing names
         *
         * @return array of arrays of Locale Namespace ids
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
         * Insert Locale Namespace record, created from an array of $info
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
         * Insert multiple Locale Namespace records, created from an array of arrays of $info
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
         * Updates a Locale Namespace record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param Model $locale_namespace record to be updated
         * @param array $info data to write to the record
         *
         * @return Model updated model
         */
        public function update(Model $locale_namespace, array $info) {

            // Update record
            return parent::_update($locale_namespace, $info);
        }

        /**
         * Delete a Locale Namespace record
         *
         * @param Model $locale_namespace record to be deleted
         *
         * @return bool
         */
        public function delete(Model $locale_namespace) {

            // Delete record
            return parent::_delete($locale_namespace);
        }

        /**
         * Delete multiple Locale Namespace records
         *
         * @param Collection $locale_namespace_collection records to be deleted
         *
         * @return bool
         */
        public function deleteMulti(Collection $locale_namespace_collection) {

            // Delete records
            return parent::_deleteMulti($locale_namespace_collection);
        }
    }
