<?php

    namespace neoform;

    class controller_account_login extends controller_index {

        public function default_action() {
            if (auth::instance()->logged_in()) {
                $bounce = http\flash::instance()->get('login_bounce');
                output::instance()->redirect($bounce ? '/' . current($bounce) : null);
            } else {
                $view = new render\view;

                $view->meta_title      = 'Login / Account';
                $view->subheader       = 'Login';
                $view->social_inactive = true;

                if ($message = http\flash::instance()->get('login_message')) {
                    $view->message = current($message);
                    http\flash::instance()->del('login_message');
                }

                if ($bounce = http\flash::instance()->get('login_bounce')) {
                    $view->bounce = current($bounce);
                }

                $view->render('account/login');
            }
        }
    }