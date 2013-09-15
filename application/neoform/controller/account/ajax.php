<?php

    namespace neoform;

    class controller_account_ajax extends controller_index {

        public function __construct() {
            locale::instance()->set_namespace('main');
            output::instance()->output_type('json');
            http::instance()->ref();
        }
    }