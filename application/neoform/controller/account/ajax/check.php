<?php

    namespace neoform;

    class controller_account_ajax_check extends controller_account_ajax {

        public function default_action() {
            $json = new render\json;

            switch (http::instance()->slug('action')) {
                //check if an email address is valid and available
                case 'email':
                    try {
                        if (user\api::email_available(http::instance()->posts())) {
                            $json->status  = 'good';
                            $json->message = "Good";
                        } else {
                            $json->status  = 'error';
                            $json->message = 'Unavailable';
                        }
                    } catch (input\exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->email;
                    }

                    break;
            }

            $json->render();
        }
    }