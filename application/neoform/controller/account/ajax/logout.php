<?php

    namespace neoform;

    class controller_account_ajax_logout extends controller_account_ajax {

        public function default_action() {

            $json = new render\json;

            if (auth::instance()->logged_in()) {
                try {
                    auth\api::logout(auth::instance());
                    $json->redirect = true;
                } catch (input\exception $e) {

                }
            } else {
                $json->redirect = true;
            }

            $json->render();
        }
    }