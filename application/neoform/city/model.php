<?php

    namespace neoform;

    /**
     * City Model
     *
     * @var int $id
     * @var int $region_id
     * @var string $name
     * @var string $name_normalized
     * @var string $name_soundex
     * @var bool $top
     * @var float $longitude
     * @var float $latitude
     */
    class city_model extends entity_record_model implements city_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'id':
                    case 'region_id':
                        return (int) $this->vars[$k];

                    // floats
                    case 'longitude':
                    case 'latitude':
                        return (float) $this->vars[$k];

                    // booleans
                    case 'top':
                        return $this->vars[$k] === 'yes';

                    // strings
                    case 'name':
                    case 'name_normalized':
                    case 'name_soundex':
                    case 'top':
                        return (string) $this->vars[$k];

                    default:
                        return $this->vars[$k];
                }
            }
        }

        /**
         * Region Model based on 'region_id'
         *
         * @return region_model
         */
        public function region() {
            return $this->_model('region', $this->vars['region_id'], 'region_model');
        }
    }
