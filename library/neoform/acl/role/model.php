<?php

    namespace neoform\acl\role;

    use neoform\entity;

    /**
     * Acl Role Model
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
         * Acl Group Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\acl\group\collection
         */
        public function acl_group_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('acl_group_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new \neoform\acl\group\collection(
                    entity::dao('neoform\acl\group\role')->by_acl_role($this->vars['id'], $order_by, $offset, $limit)
                );
            }
            return $this->_vars[$key];
        }

        /**
         * Acl Group count
         *
         * @return integer
         */
        public function acl_group_count() {
            $fieldvals = [
                'acl_role_id' => (int) $this->vars['id'],
            ];

            $key = parent::_count_var_key('acl_group_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('neoform\acl\group\role')->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * Acl Resource Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\acl\resource\collection
         */
        public function acl_resource_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('acl_resource_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new \neoform\acl\resource\collection(
                    entity::dao('neoform\acl\role\resource')->by_acl_role($this->vars['id'], $order_by, $offset, $limit)
                );
            }
            return $this->_vars[$key];
        }

        /**
         * Acl Resource count
         *
         * @return integer
         */
        public function acl_resource_count() {
            $fieldvals = [
                'acl_role_id' => (int) $this->vars['id'],
            ];

            $key = parent::_count_var_key('acl_resource_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('neoform\acl\role\resource')->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * User Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\user\collection
         */
        public function user_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('user_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new \neoform\user\collection(
                    entity::dao('neoform\user\acl\role')->by_acl_role($this->vars['id'], $order_by, $offset, $limit)
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
                'acl_role_id' => (int) $this->vars['id'],
            ];

            $key = parent::_count_var_key('user_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('neoform\user\acl\role')->count($fieldvals);
            }
            return $this->_vars[$key];
        }
    }
