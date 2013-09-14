<?php

    namespace neoform\controller;

    use neoform;

    class index extends neoform\controller {

        public function default_action() {
            neoform\core::output()->body('You do not have an index controller in your application');
        }
    }