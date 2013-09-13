<?php

    namespace neoform\acl\resource;

    use neoform\entity;

    /**
     * Acl Resource Model
     *
     * @var int $id
     * @var int|null $parent_id
     * @var string $name
     */
    class model extends entity\record\model implements definition {

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
         * @return \neoform\acl\resource\collection
         */
        public function child_acl_resource_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('child_acl_resource_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new \neoform\acl\resource\collection(
                    entity::dao('acl\resource')->by_parent($this->vars['id'], $order_by, $offset, $limit)
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
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('acl\resource')->count($fieldvals);
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
         * @return \neoform\acl\role\collection
         */
        public function acl_role_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('acl_role_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new \neoform\acl\role\collection(
                    entity::dao('acl\role\resource')->by_acl_resource($this->vars['id'], $order_by, $offset, $limit)
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
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('acl\role\resource')->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * Parent Acl Resource Model based on 'parent_id'
         *
         * @return \neoform\acl\resource\model
         */
        public function parent_acl_resource() {
            return $this->_model('parent_acl_resource', $this->vars['parent_id'], 'acl\resource\model');
        }
    }
