<?php

    namespace neoform;

    class controller_admin_acl_resource_ajax extends controller_admin {

        public function default_action() {
            output::instance()->output_type('json');
            http::instance()->ref();

            $json = new render\json;

            switch (http::instance()->slug('action')) {

                case 'insert':
                    try {
                        acl\resource\api::insert(
                            http::instance()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'ACL resource could not be created';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update':
                    try {
                        acl\resource\api::update(
                            new acl\resource\model(http::instance()->parameter('id')),
                            http::instance()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'ACL resource could not be updated';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'delete':
                    try {
                        acl\resource\api::delete(new acl\resource\model(http::instance()->parameter('id')));
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'ACL resource could not be deleted';
                        $json->errors = $e->errors();
                    }
                    break;
            }

            $json->render();
        }
    }