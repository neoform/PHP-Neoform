<?php

    namespace neoform;

    class controller_account_ajax_login extends controller_account_ajax {

        public function default_action() {

            switch (http::instance()->slug('action')) {
                case 'verify':
                    $this->verify();
                    break;
            }
        }

        protected function verify() {

            $json = new render\json;

            if (auth::instance()->logged_in()) {
                $json->status = 'good';
                if ($bounce = http\flash::instance()->get('login_bounce')) {
                    $json->bounce = current($bounce);
                    http\flash::instance()->del('login_bounce');
                }
            } else {
                try {
                    auth\api::login(
                        new site\model(config::instance()['core']['site_id']),
                        http::instance()->posts()
                    );
                    $json->status = 'good';

                    if ($bounce = http\flash::instance()->get('login_bounce')) {
                        $json->bounce = current($bounce);
                        http\flash::instance()->del('login_bounce');
                    }
                } catch (input\exception $e) {
                    sleep(1);
                    $json->message = 'Your email address or password are incorrect. Please try again.';
                }
            }

            $json->render();
        }
    }





