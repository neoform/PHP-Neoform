<?php

    namespace Neoform\Request\Parameters;

    use Neoform\Request;

    class Cookies extends Request\Parameters {

        /**
         * @param array $vals
         */
        public function __construct(array $vals) {
            parent::__construct($vals);

            foreach ($this->vals as &$cookie) {
                $cookie = base64_decode($cookie);
            }
            unset($cookie);
        }
    }