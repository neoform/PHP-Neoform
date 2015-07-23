<?php

    namespace Neoform\Acl\Group;

    use Neoform\Entity;

    /**
     * Acl Group Model
     *
     * @var int $id
     * @var string $name
     */
    class Model extends Entity\Record\Model implements Definition {

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
         * Acl Role Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (Entity\Record\Dao::SORT_ASC, Entity\Record\Dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \Neoform\Acl\Role\Collection
         */
        public function acl_role_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limitVarKey('acl_role_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new \Neoform\Acl\Role\Collection(
                    Entity::dao('Neoform\Acl\Group\Role')->by_acl_group($this->vars['id'], $order_by, $offset, $limit)
                );
            }
            return $this->_vars[$key];
        }

        /**
         * Acl Role count
         *
         * @return integer
         */
        public function acl_role_count() {
            $fieldvals = [
                'acl_group_id' => (int) $this->vars['id'],
            ];

            $key = parent::_countVarKey('acl_role_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Entity::dao('Neoform\Acl\Group\Role')->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * User Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (Entity\Record\Dao::SORT_ASC, Entity\Record\Dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \Neoform\User\Collection
         */
        public function user_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limitVarKey('user_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new \Neoform\User\Collection(
                    Entity::dao('Neoform\Acl\Group\User')->by_acl_group($this->vars['id'], $order_by, $offset, $limit)
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
                'acl_group_id' => (int) $this->vars['id'],
            ];

            $key = parent::_countVarKey('user_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Entity::dao('Neoform\Acl\Group\User')->count($fieldvals);
            }
            return $this->_vars[$key];
        }
    }
