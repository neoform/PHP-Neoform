<?php

    class controller_admin_locale_namespaces_ajax extends controller_admin {

        public function default_action() {

            core::output()->output_type('json');
            core::http()->ref();

            $json = new render_json;

            switch (core::http()->segment('action')) {

                case 'insert':
                    try {
                        locale_namespace_api::insert(
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'Namespace could not be created';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update':
                    try {
                        locale_namespace_api::update(
                            new locale_namespace_model(core::http()->parameter('id')),
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'Namespace could not be updated';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'delete':
                    try {
                        locale_namespace_api::delete(new locale_namespace_model(core::http()->parameter('id')));
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'Namespace could not be deleted';
                        $json->errors = $e->errors();
                    }
                    break;
            }

            $json->render();
        }
    }