<?php

    namespace neoform;

    class controller_account_create extends controller_index {

        public function default_action() {

            //if already logged in
            if (auth::instance()->logged_in()) {
                $bounce = http\flash::instance()->get('login_bounce');
                output::instance()->redirect($bounce ? '/' . current($bounce) : null);
            } else {
                // view variables
                $view = new render\view;

                if ($message = http\flash::instance()->get('login_message')) {
                    $view->message = current($message);
                    http\flash::instance()->del('login_message');
                }

                if ($bounce = http\flash::instance()->get('login_bounce')) {
                    $view->bounce = current($bounce);
                }

                $view->render('account/create');
            }
        }
    }