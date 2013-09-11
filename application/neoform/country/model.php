<?php

    namespace neoform;

    /**
     * Country Model
     *
     * @var int $id
     * @var string $name
     * @var string $name_normalized
     * @var string $iso2
     * @var string $iso3
     */
    class country_model extends entity_record_model implements country_definition {

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
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return region_collection
         */
        public function region_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('region_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new region_collection(
                    entity::dao('region')->by_country($this->vars['id'], $order_by, $offset, $limit)
                );
            }
            return $this->_vars[$key];
        }

        /**
         * Region Count
         *
         * @return integer
         */
        public function region_count() {
            $fieldvals = [
                'country_id' => (int) $this->vars['id'],
            ];

            $key = parent::_count_var_key('region_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('region')->count($fieldvals);
            }
            return $this->_vars[$key];
        }
    }
