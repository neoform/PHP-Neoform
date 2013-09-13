<?php

    namespace neoform;

    class controller_admin extends controller {

        public function __construct() {
            core::locale()->set_namespace('admin');
        }

        public function default_action() {
            $view = new render\view;
            $view->render('admin');
        }
    }