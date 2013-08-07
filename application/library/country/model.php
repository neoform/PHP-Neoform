<?php

    /**
    * Country Model
    *
    * @var int $id
    * @var string $name
    * @var string $name_normalized
    * @var string $iso2
    * @var string $iso3
    */
    class country_model extends record_model implements country_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'id':
                        return (int) $this->vars[$k];

                    // strings
                    case 'name':
                    case 'name_normalized':
                    case 'iso2':
                    case 'iso3':
                        return (string) $this->vars[$k];

                    default:
                        return $this->vars[$k];
                }
            }
        }

        /**
         * Region Collection
         *
         * @return region_collection
         */
        public function region_collection() {
            if (! array_key_exists('region_collection', $this->_vars)) {
                $this->_vars['region_collection'] = new region_collection(
                    entity_dao::get('region')->by_country($this->vars['id'])
                );
            }
            return $this->_vars['region_collection'];
        }
    }
