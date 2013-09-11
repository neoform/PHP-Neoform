<?php

    namespace neoform;

    /**
     * Site Model
     *
     * @var int $id
     * @var string $name
     */
    class site_model extends entity_record_model implements site_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'id':
                        return (int) $this->vars[$k];

                    // strings
                    case 'name':
                        return (string) $this->vars[$k];

                    default:
                        return $this->vars[$k];
                }
            }
        }

        /**
         * User Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return user_collection
         */
        public function user_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('user_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new user_collection(
                    entity::dao('user_site')->by_site($this->vars['id'], $order_by, $offset, $limit)
                );
            }
            return $this->_vars[$key];
        }

        /**
         * User count
         *
         * @return integer
         */
        public function user_count() {
            $fieldvals = [
                'site_id' => (int) $this->vars['id'],
            ];

            $key = parent::_count_var_key('user_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('user_site')->count($fieldvals);
            }
            return $this->_vars[$key];
        }
    }