<?php

    namespace neoform;

    class controller_account_create extends controller_index {

        public function default_action() {

            //if already logged in
            if (core::auth()->logged_in()) {
                $bounce = core::http_flash()->get('login_bounce');
                core::output()->redirect($bounce ? '/' . current($bounce) : null);
            } else {
                // view variables
                $view = new render\view;

                if ($message = core::http_flash()->get('login_message')) {
                    $view->message = current($message);
                    core::http_flash()->del('login_message');
                }

                if ($bounce = core::http_flash()->get('login_bounce')) {
                    $view->bounce = current($bounce);
                }

                $view->render('account/create');
            }
        }
    }