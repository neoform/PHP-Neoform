<?php

    /**
    * Locale Key Model
    *
    * @exception locale_key_exception
    * @var int $id
    * @var string $body
    * @var string $locale
    * @var int $namespace_id
    */
    class locale_key_model extends record_model implements locale_key_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'id':
                    case 'namespace_id':
                        return (int) $this->vars[$k];

                    // strings
                    case 'body':
                    case 'locale':
                        return (string) $this->vars[$k];

                    default:
                        return $this->vars[$k];
                }
            }

        }

        public function locale_key_message_collection() {
            if (! array_key_exists('locale_key_message_collection', $this->_vars)) {
                $this->_vars['locale_key_message_collection'] = new locale_key_message_collection(
                    locale_key_message_dao::by_key($this->vars['id'])
                );
            }
            return $this->_vars['locale_key_message_collection'];
        }

        public function locale_message_collection() {
            if (! array_key_exists('locale_message_collection', $this->_vars)) {
                $this->_vars['locale_message_collection'] = new locale_message_collection(
                    locale_message_dao::by_parent($this->vars['id'])
                );
            }
            return $this->_vars['locale_message_collection'];
        }

        public function locale() {
            return $this->_model('locale', $this->vars['locale'], 'locale_model');
        }

        public function locale_namespace() {
            return $this->_model('locale_namespace', $this->vars['namespace_id'], 'locale_namespace_model');
        }

        public function message($locale) {
            $k = 'message:' . $locale;
            if (! array_key_exists($k, $this->_vars)) {
                $this->_vars[$k] = new locale_key_message_model(
                    current(locale_key_message_dao::by_locale_key($locale, $this->id))
                );
            }
            return $this->_vars[$k];
        }
    }
