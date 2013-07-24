<?php

    class controller_account_ajax extends controller_index {

        public function __construct() {
            core::locale()->set_namespace('main');
            core::output()->output_type('json');
            core::http()->ref();
        }
    }