<?php

    namespace Neoform\Locale;

    use Neoform\Entity;
    use Neoform;

    /**
     * Locale Model
     *
     * @var string $iso2
     * @var string $name
     */
    class Model extends Entity\Record\Model {

        // Load entity details into the class
        use Details;

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // strings
                    case 'iso2':
                    case 'name':
                        return (string) $this->vars[$k];

                    default:
                        return $this->vars[$k];
                }
            }
        }

        public function get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // strings
                    case 'iso2':
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
         * @param array|null   $order_by array of field names (as the key) and sort direction (Entity\Record\Dao::SORT_ASC, Entity\Record\Dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \Neoform\Locale\Key\Collection
         */
        public function locale_key_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limitVarKey('locale_key_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = \Neoform\Locale\Key\Collection::fromPks(
                    Neoform\Locale\Key\Dao::get()->by_locale($this->vars['iso2'], $order_by, $offset, $limit)
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
                'locale' => (string) $this->vars['iso2'],
            ];

            $key = parent::_countVarKey('locale_key_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Neoform\Locale\Key\Dao::get()->count($fieldvals);
            }
            return $this->_vars[$key];
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
                    Neoform\Locale\Key\Message\Dao::get()->by_locale($this->vars['iso2'], $order_by, $offset, $limit)
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
                'locale' => (string) $this->vars['iso2'],
            ];

            $key = parent::_countVarKey('locale_key_message_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Neoform\Locale\Key\Message\Dao::get()->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * Locale Key Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (Entity\Record\Dao::SORT_ASC, Entity\Record\Dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \Neoform\Locale\Key\Collection
         */
        public function locale_key_collection1(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limitVarKey('locale_key_collection1', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = \Neoform\Locale\Key\Collection::fromPks(
                    Neoform\Locale\Key\Message\Dao::get()->by_locale($this->vars['iso2'], $order_by, $offset, $limit)
                );
            }
            return $this->_vars[$key];
        }

        /**
         * Locale Key count
         *
         * @return integer
         */
        public function locale_key_count1() {
            $fieldvals = [
                'locale' => (string) $this->vars['iso2'],
            ];

            $key = parent::_countVarKey('locale_key_count1', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Neoform\Locale\Key\Message\Dao::get()->count($fieldvals);
            }
            return $this->_vars[$key];
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
                $this->_vars[$k] = \Neoform\Locale\Key\Message\Model::fromPk(
                    current(Neoform\Locale\Key\Message\Dao::get()->by_locale_key($locale, $this->id))
                );
            }
            return $this->_vars[$k];
        }
    }
