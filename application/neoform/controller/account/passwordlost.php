<?php

    namespace neoform;

    class controller_account_passwordlost extends controller_index {

        public function default_action() {
            if (auth::instance()->logged_in()) {
                output::instance()->redirect();
            } else {
                $view = new render\view;
                $view->render('account/password_lost');
            }
        }
    }