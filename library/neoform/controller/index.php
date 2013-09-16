<?php

    namespace neoform;

    class controller_index extends http\controller {

        public function default_action() {
            output::instance()->body('You do not have an index controller in your application');
        }
    }