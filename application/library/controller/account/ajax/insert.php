<?php

    class controller_account_ajax_insert extends controller_account_ajax {

        public function default_action() {

            $json = new render_json();

            //if already logged in
            if (core::auth()->logged_in()) {
                $json->status = 'good';
            } else {
                try {
                    // Create user
                    $user = user_api::insert(core::http()->posts());
                    $site = new site_model(core::config()->system['site_id']);

                    // Create user-site link
                    user_site_dao::insert([
                        'user_id' => $user->id,
                        'site_id' => $site->id,
                    ]);

                    // Activate session
                    auth_lib::activate_session($user, (bool) core::http()->post('remember'));

                    $json->status = 'good';
                } catch (input_exception $e) {
                    $json->errors = $e->errors();
                    $json->message = $e->message();
                }
            }

            $json->render();
        }
    }