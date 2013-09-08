<?php

    namespace neoform;

    class controller_account_ajax_update extends controller_account_ajax {

        public function default_action() {

            $json = new render_json;

            if (core::auth()->logged_in()) {
                try {
                    address_lib::make(
                        core::auth()->user_id,
                        [
                            'label'       => core::http()->post('label'),
                            'address1'    => core::http()->post('address1'),
                            'address2'    => core::http()->post('address2'),
                            'country_id'  => core::http()->post('country'),
                            'province_id' => core::http()->post('province'),
                            'city_id'     => core::http()->post('city'),
                            'postal'      => core::http()->post('postal'),
                        ]
                    );
                    $json->status = 'good';
                } catch (input_exception $e) {
                    $json->message = $e->message();
                    $json->errors  = $e->errors();
                }
            } else {
                $json->message = 'You are not logged in';
            }

            $json->render();
        }
    }