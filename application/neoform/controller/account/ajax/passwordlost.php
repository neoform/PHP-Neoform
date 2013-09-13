<?php

    namespace neoform;

    class controller_account_ajax_passwordlost extends controller_account_ajax {

        public function default_action() {
            if (core::auth()->logged_in()) {
                throw new error\exception('You are already logged in');
            } else {
                $json = new render\json;

                try {
                    user\lostpassword\api::lost(
                        new site\model(core::config()['core']['site_id']),
                        core::http()->posts()
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