<?php

    namespace Neoform\Locale\Key\Message;

    use Neoform\Entity;

    /**
     * Locale Key Message Model
     *
     * @var int $id
     * @var int $key_id
     * @var string $body
     * @var string $locale
     */
    class Model extends Entity\Record\Model implements Definition {

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
         * @return \Neoform\Locale\Key\Model
         */
        public function locale_key() {
            return $this->_model('locale_key', $this->vars['key_id'], 'Neoform\Locale\Key\Model');
        }

        /**
         * Locale Model based on 'locale'
         *
         * @return \Neoform\Locale\Model
         */
        public function locale() {
            return $this->_model('locale', $this->vars['locale'], 'Neoform\Locale\Model');
        }
    }
