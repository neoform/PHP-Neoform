<?php

    namespace Neoform\Input;

    use Neoform;

    interface Validator {

        /**
         * Validates an input collection
         *
         * @param Collection $input
         */
        public function validate(Neoform\Input\Collection $input);
    }