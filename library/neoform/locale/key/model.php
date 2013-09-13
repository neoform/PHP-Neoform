<?php

    namespace neoform\locale\key;

    use neoform\entity;

    /**
     * Locale Key Model
     *
     * @var int $id
     * @var string $body
     * @var string $locale
     * @var int $namespace_id
     */
    class model extends entity\record\model implements definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'id':
                    case 'namespace_id':
                        return (int) $this->vars[$k];

                    // strings
                    case 'body':
                    case 'locale':
                        return (string) $this->vars[$k];

                    default:
                        return $this->vars[$k];
                }
            }
        }

        /**
         * Locale Key Message Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\locale\key\message\collection
         */
        public function locale_key_message_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('locale_key_message_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new \neoform\locale\key\message\collection(
                    entity::dao('locale\key\message')->by_key($this->vars['id'], $order_by, $offset, $limit)
                );
            }
            return $this->_vars[$key];
        }

        /**
         * Locale Key Message Count
         *
         * @return integer
         */
        public function locale_key_message_count() {
            $fieldvals = [
                'key_id' => (int) $this->vars['id'],
            ];

            $key = parent::_count_var_key('locale_key_message_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('locale\key\message')->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * Locale Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\locale\collection
         */
        public function locale_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('locale_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new \neoform\locale\collection(
                    entity::dao('locale\key\message')->by_key($this->vars['id'], $order_by, $offset, $limit)
                );
            }
            return $this->_vars[$key];
        }

        /**
         * Locale count
         *
         * @return integer
         */
        public function locale_count() {
            $fieldvals = [
                'key_id' => (int) $this->vars['id'],
            ];

            $key = parent::_count_var_key('locale_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('locale\key\message')->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * Locale Model based on 'locale'
         *
         * @return \neoform\locale\model
         */
        public function locale() {
            return $this->_model('locale', $this->vars['locale'], 'locale\model');
        }

        /**
         * Locale Namespace Model based on 'namespace_id'
         *
         * @return \neoform\locale\nspace\model
         */
        public function locale_namespace() {
            return $this->_model('locale_namespace', $this->vars['namespace_id'], 'locale\nspace\model');
        }

        /**
         * Get a key's message model
         *
         * @param string $locale
         *
         * @return string
         */
        public function message($locale) {
            $k = "message:{$locale}";
            if (! array_key_exists($k, $this->_vars)) {
                $this->_vars[$k] = new message\model(
                    current(entity::dao('locale_key_message')->by_locale_key($locale, $this->id))
                );
            }
            return $this->_vars[$k];
        }
    }
