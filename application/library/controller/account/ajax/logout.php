<?php

    class controller_account_ajax_logout extends controller_account_ajax {

        public function default_action() {

            $json = new render_json;

            if (core::auth()->logged_in()) {
                try {
                    auth_api::logout(core::auth());
                    $json->redirect = true;
                } catch (input_exception $e) {

                }
            } else {
                $json->redirect = true;
            }

            $json->render();
        }
    }