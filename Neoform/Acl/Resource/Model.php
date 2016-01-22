<?php

    namespace Neoform\Acl\Resource;

    use Neoform\Entity;
    use Neoform;

    /**
     * Acl Resource Model
     *
     * @var int $id
     * @var int|null $parent_id
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

        public function get($k) {

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
         * Get the slug of the resource
         *
         * @return string
         */
        public function slug() {
            if (! array_key_exists('slug', $this->_vars)) {
                $arr = [];
                foreach ($this->ancestors() as $resource) {
                    $arr[] = $resource->name;
                }
                $arr[] = $this->name;
                $this->_vars['slug'] = join('/', $arr);
            }

            return $this->_vars['slug'];
        }

        /**
         * Get the ancestor (parents) of this resource
         *
         * @return array
         */
        public function ancestors() {
            if (! array_key_exists('ancestors', $this->_vars)) {
                $ancestors = [];
                $resource  = $this;
                while ($resource->parent_id && $resource = $resource->parent_acl_resource()) {
                    array_unshift($ancestors, $resource);
                }
                $this->_vars['ancestors'] = $ancestors;
            }

            return $this->_vars['ancestors'];
        }

        /**
         * Child Acl Resource Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (Entity\Record\Dao::SORT_ASC, Entity\Record\Dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \Neoform\Acl\Resource\Collection
         */
        public function child_acl_resource_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limitVarKey('child_acl_resource_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = \Neoform\Acl\Resource\Collection::fromPks(
                    Neoform\Acl\Resource\Dao::get()->by_parent($this->vars['id'], $order_by, $offset, $limit)
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

            $key = parent::_countVarKey('child_acl_resource_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Neoform\Acl\Resource\Dao::get()->count($fieldvals);
            }
            return $this->_vars[$key];
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
                $this->_vars[$key] = \Neoform\Acl\Role\Collection::fromPks(
                    Neoform\Acl\Role\Resource\Dao::get()->by_acl_resource($this->vars['id'], $order_by, $offset, $limit)
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

            $key = parent::_countVarKey('acl_role_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Neoform\Acl\Role\Resource\Dao::get()->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * Parent Acl Resource Model based on 'parent_id'
         *
         * @return \Neoform\Acl\Resource\Model
         */
        public function parent_acl_resource() {
            return $this->_model('parent_acl_resource', $this->vars['parent_id'], 'Neoform\Acl\Resource\Model');
        }
    }
