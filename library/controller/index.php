<?php

    class controller_index extends controller {
        public function default_action() {
            core::output()->body('You do not have an index controller in your application');
        }
    }