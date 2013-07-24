<?php

    class controller_account_create extends controller_index {

        public function default_action() {

            //if already logged in
            if (core::auth()->logged_in()) {
                core::output()->redirect(core::flash()->get('login_bounce'));
            } else {
                // view variables
                $view = new render_view;
                $view->render('account/create');
            }
        }
    }