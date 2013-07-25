<?php

    class controller_account_login extends controller_index {

        public function default_action() {
            if (core::auth()->logged_in()) {
                $bounce = core::flash()->get('login_bounce');
                core::output()->redirect($bounce ? '/' . current($bounce) : null);
            } else {
                $view = new render_view;

                $view->meta_title      = 'Login / Account';
                $view->subheader       = 'Login';
                $view->social_inactive = true;

                if ($message = core::flash()->get('login_message')) {
                    $view->message = current($message);
                    core::flash()->del('login_message');
                }

                if ($bounce = core::flash()->get('login_bounce')) {
                    $view->bounce = current($bounce);
                }

                $view->render('account/login');
            }
        }
    }