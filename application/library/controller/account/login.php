<?php

    class controller_account_login extends controller_index {

        public function default_action() {
            if (core::auth()->logged_in()) {
                core::output()->redirect(core::flash()->get('login_bounce'));
            } else {
                $view = new render_view;

                $view->meta_title      = 'Login / Account';
                $view->subheader       = 'Login';
                $view->social_inactive = true;

                $message = core::flash()->get('login_message');
                $bounce  = core::flash()->get('login_bounce');

                if ($message) {
                    $view->message = $message;
                    core::flash()->del('login_message');
                }

                if ($bounce) {
                    $view->bounce = $bounce;
                    core::flash()->del('login_bounce');
                }

                $view->render('account/login');
            }
        }
    }