<?php

    new account_ajax_login();

    class account_ajax_login {

        public function __construct() {

            core::output()->output_type('json');

            switch (core::http()->segment(4)) {

                case 'verify':
                    $this->verify();
                    break;
            }
        }

        protected function verify() {
            core::http()->ref();

            $json = new render_json();

            if (core::auth()->user_id) {
                $json->status = 'good';
            } else {
                try {
                    auth_api::login(
                        new site_model(core::config()->system['site_id']),
                        core::http()->posts()
                    );
                    $json->status = 'good';

                    // if it exists, delete the login bounce
                    core::flash()->del('login_bounce');
                } catch (input_exception $e) {
                    sleep(1);
                    $json->message = 'Your email address or password are incorrect. Please try again.';
                }
            }

            $json->render();
        }
    }





