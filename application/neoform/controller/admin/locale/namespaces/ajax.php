<?php

    namespace neoform;

    class controller_admin_locale_namespaces_ajax extends controller_admin {

        public function default_action() {

            output::instance()->output_type('json');
            http::instance()->ref();

            $json = new render\json;

            switch (http::instance()->slug('action')) {

                case 'insert':
                    try {
                        locale\nspace\api::insert(
                            http::instance()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'Namespace could not be created';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update':
                    try {
                        locale\nspace\api::update(
                            new locale\nspace\model(http::instance()->parameter('id')),
                            http::instance()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'Namespace could not be updated';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'delete':
                    try {
                        locale\nspace\api::delete(new locale\nspace\model(http::instance()->parameter('id')));
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'Namespace could not be deleted';
                        $json->errors = $e->errors();
                    }
                    break;
            }

            $json->render();
        }
    }