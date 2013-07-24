<?php

    class controller_index extends controller {

        public function __construct() {
            core::locale()->set_namespace('main');
        }

        public function default_action() {
            $view = new render_view;
            $view->render('index');
        }
    }