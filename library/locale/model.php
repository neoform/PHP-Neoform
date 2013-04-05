<?php

    /**
    * Locale Model
    *
    * @exception locale_exception
    * @var string $iso2
    * @var string $name
    */
    class locale_model extends record_model implements locale_definition {

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

        public function locale_key_collection() {
            if (! array_key_exists('locale_key_collection', $this->_vars)) {
                $this->_vars['locale_key_collection'] = new locale_key_collection(
                    locale_key_dao::by_locale($this->vars['iso2'])
                );
            }
            return $this->_vars['locale_key_collection'];
        }

        public function locale_key_message_collection() {
            if (! array_key_exists('locale_key_message_collection', $this->_vars)) {
                $this->_vars['locale_key_message_collection'] = new locale_key_message_collection(
                    locale_key_message_dao::by_locale($this->vars['iso2'])
                );
            }
            return $this->_vars['locale_key_message_collection'];
        }

        public function locale_message_collection() {
            if (! array_key_exists('locale_message_collection', $this->_vars)) {
                $this->_vars['locale_message_collection'] = new locale_message_collection(
                    locale_message_dao::by_locale($this->vars['iso2'])
                );
            }
            return $this->_vars['locale_message_collection'];
        }
    }
