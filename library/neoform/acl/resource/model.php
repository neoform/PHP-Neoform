<?php

    namespace neoform;

    /**
     * Acl Resource Model
     *
     * @var int $id
     * @var int|null $parent_id
     * @var string $name
     */
    class acl_resource_model extends entity_record_model implements acl_resource_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'id':
                    case 'parent_id':
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
         * Child Acl Resource Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return acl_resource_collection
         */
        public function child_acl_resource_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('child_acl_resource_collection', $order_by, $offset, $limit);
            if (! \array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new acl_resource_collection(
                    entity::dao('acl_resource')->by_parent($this->vars['id'], $order_by, $offset, $limit)
                );
            }
            return $this->_vars[$key];
        }

        /**
         * Child Acl Resource Count
         *
         * @return integer
         */
        public function child_acl_resource_count() {
            $fieldvals = [
                'parent_id' => (int) $this->vars['id'],
            ];

            $key = parent::_count_var_key('child_acl_resource_count', $fieldvals);
            if (! \array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('acl_resource')->count($fieldvals);
            }
            return $this->_vars[$key];
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
            if (! \array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new acl_role_collection(
                    entity::dao('acl_role_resource')->by_acl_resource($this->vars['id'], $order_by, $offset, $limit)
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
                'acl_resource_id' => (int) $this->vars['id'],
            ];

            $key = parent::_count_var_key('acl_role_count', $fieldvals);
            if (! \array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('acl_role_resource')->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * Parent Acl Resource Model based on 'parent_id'
         *
         * @return acl_resource_model
         */
        public function parent_acl_resource() {
            return $this->_model('parent_acl_resource', $this->vars['parent_id'], 'acl_resource_model');
        }
    }
