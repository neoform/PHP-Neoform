<?php

    namespace Neoform\Response;

    interface Response {

        /**
         * Renders the output (eg, submits headers and echos the body)
         */
        public function render();
    }