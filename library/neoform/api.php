<?php

    namespace neoform;

    abstract class api {

        protected $input;

        /**
         * Set/get the input
         *
         * @param input\collection|array $input
         *
         * @return input\collection|array|null
         *
         * @throws \exception
         */
        public function input($input=null) {
            if ($input) {
                if ($input instanceof input\collection) {
                    $this->input = $input;
                } else if (is_array($input)) {
                    $this->input = new input\collection($input);
                } else {
                    throw new \exception('invalid input type');
                }
            } else {
                return $this->input;
            }
        }
    }