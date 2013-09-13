<?php

    namespace neoform\locale\key\message;

    use neoform\entity;

    /**
     * Locale Key Message Model
     *
     * @var int $id
     * @var int $key_id
     * @var string $body
     * @var string $locale
     */
    class model extends entity\record\model implements definition {

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
         * @return \neoform\locale\key\model
         */
        public function locale_key() {
            return $this->_model('locale_key', $this->vars['key_id'], 'locale\key\model');
        }

        /**
         * Locale Model based on 'locale'
         *
         * @return \neoform\locale\model
         */
        public function locale() {
            return $this->_model('locale', $this->vars['locale'], 'locale\model');
        }
    }
