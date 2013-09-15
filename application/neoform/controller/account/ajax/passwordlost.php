<?php

    namespace neoform;

    class controller_account_ajax_passwordlost extends controller_account_ajax {

        public function default_action() {
            if (auth::instance()->logged_in()) {
                throw new error\exception('You are already logged in');
            } else {
                $json = new render\json;

                try {
                    user\lostpassword\api::lost(
                        new site\model(config::instance()['core']['site_id']),
                        http::instance()->posts()
                    );
                    $json->status = 'good';
                } catch (input\exception $e) {
                    $json->message = $e->message();
                    $json->errors  = $e->errors();
                }
                $json->render();
            }
        }
    }