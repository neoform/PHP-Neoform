<?php

    namespace neoform;

    class controller_index extends http\controller {

        public function __construct() {
            core::locale()->set_namespace('main');
        }

        public function default_action() {
            $view = new render\view;
            $view->render('index');
        }
    }