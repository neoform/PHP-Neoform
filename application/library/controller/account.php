<?php

    class controller_account extends controller {

        public function __construct() {
            core::locale()->set_namespace('main');

            if (! core::auth()->logged_in()) {
                // @todo replace 'account' with something better
                throw new redirect_login_exception('account');
            }
        }

        public function default_action() {
            $view = new render_view;
            $view->meta_title = 'Account Dashboard';
            $view->render('account');
        }
    }

