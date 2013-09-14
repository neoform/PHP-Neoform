<?php

    namespace neoform\region;

    use neoform\entity;

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
    class model extends entity\record\model implements definition {

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
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\city\collection
         */
        public function city_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('city_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new \neoform\city\collection(
                    entity::dao('city')->by_region($this->vars['id'], $order_by, $offset, $limit)
                );
            }
            return $this->_vars[$key];
        }

        /**
         * City Count
         *
         * @return integer
         */
        public function city_count() {
            $fieldvals = [
                'region_id' => (int) $this->vars['id'],
            ];

            $key = parent::_count_var_key('city_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('city')->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * Country Model based on 'country_id'
         *
         * @return \neoform\country\model
         */
        public function country() {
            return $this->_model('country', $this->vars['country_id'], 'country\model');
        }
    }
