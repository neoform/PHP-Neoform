<?php

    class controller_admin_locale_namespaces_messages_ajax extends controller_admin {

        public function default_action() {

            core::output()->output_type('json');
            core::http()->ref();

            $json = new render_json;

            switch (core::http()->segment(6)) {

                case 'insert':
                    try {
                        locale_key_api::insert(
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'Translation could not be created';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update':
                    try {
                        locale_key_message_api::update(
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'Translation could not be updated';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'delete':
                    try {
                        locale_key_api::delete(new locale_key_model(core::http()->parameter('id')));
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'Translation could not be deleted';
                        $json->errors = $e->errors();
                    }
                    break;
            }

            $json->render();
        }
    }