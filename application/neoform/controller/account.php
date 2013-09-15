<?php

    namespace neoform;

    class controller_account extends http\controller {

        public function __construct() {
            locale::instance()->set_namespace('main');

            if (! auth::instance()->logged_in()) {
                throw new redirect\login\exception(http::instance()->server('query'));
            }
        }

        public function default_action() {
            $view = new render\view;
            $view->meta_title = 'Account Dashboard';
            $view->render('account');
        }
    }

