<?php

    class controller_account_ajax_login extends controller_account_ajax {

        public function default_action() {

            switch (core::http()->segment('action')) {
                case 'verify':
                    $this->verify();
                    break;
            }
        }

        protected function verify() {

            $json = new render_json;

            if (core::auth()->logged_in()) {
                $json->status = 'good';
                if ($bounce = core::flash()->get('login_bounce')) {
                    $json->bounce = current($bounce);
                    core::flash()->del('login_bounce');
                }
            } else {
                try {
                    auth_api::login(
                        new site_model(core::config()->system['site_id']),
                        core::http()->posts()
                    );
                    $json->status = 'good';

                    if ($bounce = core::flash()->get('login_bounce')) {
                        $json->bounce = current($bounce);
                        core::flash()->del('login_bounce');
                    }
                } catch (input_exception $e) {
                    sleep(1);
                    $json->message = 'Your email address or password are incorrect. Please try again.';
                }
            }

            $json->render();
        }
    }





