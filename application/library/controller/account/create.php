<?php

    class controller_account_create extends controller_index {

        public function default_action() {

            //if already logged in
            if (core::auth()->logged_in()) {
                core::output()->redirect('/' . current(core::flash()->get('login_bounce')));
            } else {
                // view variables
                $view = new render_view;

                if ($message = core::flash()->get('login_message')) {
                    $view->message = current($message);
                    core::flash()->del('login_message');
                }

                if ($bounce = core::flash()->get('login_bounce')) {
                    $view->bounce = current($bounce);
                }

                $view->render('account/create');
            }
        }
    }