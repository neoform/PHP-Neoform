<?php

    namespace neoform;

    /**
     * Acl Group Model
     *
     * @var int $id
     * @var string $name
     */
    class acl_group_model extends entity_record_model implements acl_group_definition {

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
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return acl_role_collection
         */
        public function acl_role_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('acl_role_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new acl_role_collection(
                    entity::dao('acl_group_role')->by_acl_group($this->vars['id'], $order_by, $offset, $limit)
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

            $key = parent::_count_var_key('acl_role_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('acl_group_role')->count($fieldvals);
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
         * @return user_collection
         */
        public function user_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('user_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new user_collection(
                    entity::dao('acl_group_user')->by_acl_group($this->vars['id'], $order_by, $offset, $limit)
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

            $key = parent::_count_var_key('user_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('acl_group_user')->count($fieldvals);
            }
            return $this->_vars[$key];
        }
    }
