<?php

    namespace neoform;

    class controller_admin_locale_namespaces_messages_ajax extends controller_admin {

        public function default_action() {

            output::instance()->output_type('json');
            http::instance()->ref();

            $json = new render\json;

            switch (http::instance()->segment('action')) {

                case 'insert':
                    try {
                        locale\key\api::insert(
                            http::instance()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'Translation could not be created';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update':
                    try {
                        locale\key\message\api::update(
                            http::instance()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'Translation could not be updated';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'delete':
                    try {
                        locale\key\api::delete(new locale\key\model(http::instance()->parameter('id')));
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'Translation could not be deleted';
                        $json->errors = $e->errors();
                    }
                    break;
            }

            $json->render();
        }
    }