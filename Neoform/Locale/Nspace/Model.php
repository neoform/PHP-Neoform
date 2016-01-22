<?php

    namespace Neoform\Locale\Nspace;

    use Neoform\Entity;
    use Neoform;

    /**
     * Locale Namespace Model
     *
     * @var int $id
     * @var string $name
     */
    class Model extends Entity\Record\Model {

        // Load entity details into the class
        use Details;

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

        public function get($k) {

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
                    Neoform\Locale\Key\Dao::get()->by_namespace($this->vars['id'], $order_by, $offset, $limit)
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

            $key = parent::_countVarKey('locale_key_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Neoform\Locale\Key\Dao::get()->count($fieldvals);
            }
            return $this->_vars[$key];
        }
    }
