<?php

    namespace Neoform;

    abstract class Api {

        protected $input;

        /**
         * Set/get the input
         *
         * @param Input\Collection|array $input
         *
         * @return Input\Collection|array|null
         *
         * @throws \Exception
         */
        public function input($input=null) {
            if ($input) {
                if ($input instanceof Input\Collection) {
                    $this->input = $input;
                } else if (is_array($input)) {
                    $this->input = new Input\Collection($input);
                } else {
                    throw new \Exception('invalid input type');
                }
            } else {
                return $this->input;
            }
        }
    }