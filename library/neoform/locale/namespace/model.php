<?php

    namespace neoform\locale\npace;

    use neoform\entity;

    /**
     * Locale Namespace Model
     *
     * @var int $id
     * @var string $name
     */
    class model extends entity\record\model implements definition {

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
         * Locale Key Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\locale\key\collection
         */
        public function locale_key_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('locale_key_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new \neoform\locale\key\collection(
                    entity::dao('neoform\locale\key')->by_namespace($this->vars['id'], $order_by, $offset, $limit)
                );
            }
            return $this->_vars[$key];
        }

        /**
         * Locale Key Count
         *
         * @return integer
         */
        public function locale_key_count() {
            $fieldvals = [
                'namespace_id' => (int) $this->vars['id'],
            ];

            $key = parent::_count_var_key('locale_key_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('neoform\locale\key')->count($fieldvals);
            }
            return $this->_vars[$key];
        }
    }
