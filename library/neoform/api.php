<?php

    namespace neoform;

    abstract class api {

        protected $input;

        /**
         * Set the input
         *
         * @param input\collection|array $input
         *
         * @throws \exception
         */
        public function input($input) {
            if ($input instanceof input\collection) {
                $this->input = $input;
            } else if (is_array($input)) {
                $this->input = new input\collection($input);
            } else {
                throw new \exception('invalid input type');
            }
        }
    }