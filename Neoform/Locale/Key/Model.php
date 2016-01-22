<?php

    namespace Neoform\Locale\Key;

    use Neoform\Entity;
    use Neoform;

    /**
     * Locale Key Model
     *
     * @var int $id
     * @var string $body
     * @var string $locale
     * @var int $namespace_id
     */
    class Model extends Entity\Record\Model {

        // Load entity details into the class
        use Details;

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

        public function get($k) {

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
         * @param array|null   $order_by array of field names (as the key) and sort direction (Entity\Record\Dao::SORT_ASC, Entity\Record\Dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \Neoform\Locale\Key\Message\Collection
         */
        public function locale_key_message_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limitVarKey('locale_key_message_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = \Neoform\Locale\Key\Message\Collection::fromPks(
                    Neoform\Locale\Key\Message\Dao::get()->by_key($this->vars['id'], $order_by, $offset, $limit)
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

            $key = parent::_countVarKey('locale_key_message_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Neoform\Locale\Key\Message\Dao::get()->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * Locale Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (Entity\Record\Dao::SORT_ASC, Entity\Record\Dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \Neoform\Locale\Collection
         */
        public function locale_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limitVarKey('locale_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = \Neoform\Locale\Collection::fromPks(
                    Neoform\Locale\Key\Message\Dao::get()->by_key($this->vars['id'], $order_by, $offset, $limit)
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

            $key = parent::_countVarKey('locale_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Neoform\Locale\Key\Message\Dao::get()->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * Locale Model based on 'locale'
         *
         * @return \Neoform\Locale\Model
         */
        public function locale() {
            return $this->_model('locale', $this->vars['locale'], 'Neoform\Locale\Model');
        }

        /**
         * Locale Namespace Model based on 'namespace_id'
         *
         * @return \Neoform\Locale\Nspace\Model
         */
        public function locale_namespace() {
            return $this->_model('locale_namespace', $this->vars['namespace_id'], 'Neoform\Locale\Nspace\Model');
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
                $this->_vars[$k] = Message\Model::fromPk(
                    current(Neoform\Locale\Key\Message\Dao::get()->by_locale_key($locale, $this->id))
                );
            }
            return $this->_vars[$k];
        }
    }
