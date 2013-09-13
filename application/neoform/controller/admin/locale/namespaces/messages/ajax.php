<?php

    namespace neoform;

    class controller_admin_locale_namespaces_messages_ajax extends controller_admin {

        public function default_action() {

            core::output()->output_type('json');
            core::http()->ref();

            $json = new render\json;

            switch (core::http()->segment('action')) {

                case 'insert':
                    try {
                        locale\key\api::insert(
                            core::http()->posts()
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
                            new locale\key\message\model(core::http()->parameter('id')),
                            core::http()->posts()
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
                        locale\key\api::delete(new locale\key\model(core::http()->parameter('id')));
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