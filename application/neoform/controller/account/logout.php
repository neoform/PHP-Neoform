<?php

    namespace neoform;

    class controller_account_logout extends controller_index {

        public function default_action() {

            http::instance()->ref();

            //verify
            if (auth::instance()->logged_in()) {
                try {
                    auth\api::logout(auth::instance());
                } catch (input\exception $e) {

                }
            }

            output::instance()->redirect();
        }
    }