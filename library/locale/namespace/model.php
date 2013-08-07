<?php

    /**
    * Locale Namespace Model
    *
    * @var int $id
    * @var string $name
    */
    class locale_namespace_model extends record_model implements locale_namespace_definition {

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
         * Locale Key Collection
         *
         * @return locale_key_collection
         */
        public function locale_key_collection() {
            if (! array_key_exists('locale_key_collection', $this->_vars)) {
                $this->_vars['locale_key_collection'] = new locale_key_collection(
                    entity_dao::get('locale_key')->by_namespace($this->vars['id'])
                );
            }
            return $this->_vars['locale_key_collection'];
        }
    }
