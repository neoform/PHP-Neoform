<?php

    namespace Neoform\Email;

    use Neoform;

    class Config extends Neoform\Config\Model {

        /**
         * @return string
         */
        public function getUnsubscribeSecret() {
            return $this->values['unsubscribe_secret'];
        }
    }