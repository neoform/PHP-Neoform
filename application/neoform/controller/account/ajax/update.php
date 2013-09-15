<?php

    namespace neoform;

    class controller_account_ajax_update extends controller_account_ajax {

        public function default_action() {

            $json = new render\json;

            if (auth::instance()->logged_in()) {
                try {
                    address\lib::make(
                        auth::instance()->user_id,
                        [
                            'label'       => http::instance()->post('label'),
                            'address1'    => http::instance()->post('address1'),
                            'address2'    => http::instance()->post('address2'),
                            'country_id'  => http::instance()->post('country'),
                            'province_id' => http::instance()->post('province'),
                            'city_id'     => http::instance()->post('city'),
                            'postal'      => http::instance()->post('postal'),
                        ]
                    );
                    $json->status = 'good';
                } catch (input\exception $e) {
                    $json->message = $e->message();
                    $json->errors  = $e->errors();
                }
            } else {
                $json->message = 'You are not logged in';
            }

            $json->render();
        }
    }