<?php

    namespace neoform\http\controller;

    use neoform;

    class index extends neoform\http\controller {

        public function default_action() {
            neoform\core::output()->body('You do not have an index controller in your application');
        }
    }