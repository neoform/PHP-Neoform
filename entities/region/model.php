<?php

    /**
    * Region Model
    *
    * @var int $id
    * @var int $country_id
    * @var string $name
    * @var string $name_normalized
    * @var string $name_soundex
    * @var string $iso2
    * @var float $longitude
    * @var float $latitude
    */
    class region_model extends record_model implements region_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'id':
                    case 'country_id':
                        return (int) $this->vars[$k];

                    // floats
                    case 'longitude':
                    case 'latitude':
                        return (float) $this->vars[$k];

                    // strings
                    case 'name':
                    case 'name_normalized':
                    case 'name_soundex':
                    case 'iso2':
                        return (string) $this->vars[$k];

                    default:
                        return $this->vars[$k];
                }
            }

        }

        /**
         * City Collection
         *
         * @param city_collection $city_collection preload collection
         *
         * @return city_collection
         */
        public function city_collection(city_collection $city_collection=null) {
            if (! array_key_exists('city_collection', $this->_vars)) {
                if ($city_collection !== null) {
                    $this->_vars['city_collection'] = $city_collection;
                } else {
                    $this->_vars['city_collection'] = new city_collection(
                        city_dao::by_region($this->vars['id'])
                    );
                }
            }
            return $this->_vars['city_collection'];
        }

        /**
         * Country Model based on 'country_id'
         *
         * @param country_model $country preload model
         *
         * @return country_model
         */
        public function country(country_model $country=null) {
            return $country !== null ? ($this->_vars['country'] = $country) : $this->_model('country', $this->vars['country_id'], 'country_model');
        }

    }
