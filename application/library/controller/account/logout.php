<?php

    class controller_account_logout extends controller_index {

        public function default_action() {

            core::http()->ref();

            //verify
            if (core::auth()->logged_in()) {
                try {
                    auth_api::logout(core::auth());
                } catch (input_exception $e) {

                }
            }

            core::output()->redirect();
        }
    }