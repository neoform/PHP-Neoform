<?php

    namespace neoform;

    /**
     * Locale Key Message Model
     *
     * @var int $id
     * @var int $key_id
     * @var string $body
     * @var string $locale
     */
    class locale_key_message_model extends entity_record_model implements locale_key_message_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'id':
                    case 'key_id':
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

        /**
         * Locale Key Model based on 'key_id'
         *
         * @return locale_key_model
         */
        public function locale_key() {
            return $this->_model('locale_key', $this->vars['key_id'], 'locale_key_model');
        }

        /**
         * Locale Model based on 'locale'
         *
         * @return locale_model
         */
        public function locale() {
            return $this->_model('locale', $this->vars['locale'], 'locale_model');
        }
    }
