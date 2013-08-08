<?php

    /**
    * Locale Model
    *
    * @var string $iso2
    * @var string $name
    */
    class locale_model extends entity_record_model implements locale_definition {

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

        /**
         * Locale Key Collection
         *
         * @return locale_key_collection
         */
        public function locale_key_collection() {
            if (! array_key_exists('locale_key_collection', $this->_vars)) {
                $this->_vars['locale_key_collection'] = new locale_key_collection(
                    entity::dao('locale_key')->by_locale($this->vars['iso2'])
                );
            }
            return $this->_vars['locale_key_collection'];
        }

        /**
         * Locale Key Message Collection
         *
         * @return locale_key_message_collection
         */
        public function locale_key_message_collection() {
            if (! array_key_exists('locale_key_message_collection', $this->_vars)) {
                $this->_vars['locale_key_message_collection'] = new locale_key_message_collection(
                    entity::dao('locale_key_message')->by_locale($this->vars['iso2'])
                );
            }
            return $this->_vars['locale_key_message_collection'];
        }

        /**
         * Locale Key Collection 2
         *
         * @return locale_key_collection
         */
        public function locale_key_collection2() {
            if (! array_key_exists('locale_key_collection2', $this->_vars)) {
                $this->_vars['locale_key_collection2'] = new locale_key_collection(
                    entity::dao('locale_key_message')->by_locale($this->vars['iso2'])
                );
            }
            return $this->_vars['locale_key_collection2'];
        }
    }
