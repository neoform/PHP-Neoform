<?php

    namespace neoform;

    class controller_admin_acl_role_ajax extends controller_admin {

        public function default_action() {
            output::instance()->output_type('json');
            http::instance()->ref();

            $json = new render\json;

            switch (http::instance()->segment('action')) {

                case 'insert':
                    try {
                        acl\role\api::insert(
                            http::instance()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->message() ? $e->message() : 'ACL role could not be created';
                        $json->errors  = $e->errors();
                    }
                    break;

                case 'update':
                    try {
                        acl\role\api::update(
                            new acl\role\model(http::instance()->parameter('id')),
                            http::instance()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->message() ? $e->message() : 'ACL role could not be updated';
                        $json->errors  = $e->errors();
                    }
                    break;

                case 'delete':
                    try {
                        acl\role\api::delete(
                            new acl\role\model(http::instance()->parameter('id'))
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->message() ? $e->message() : 'ACL role could not be deleted';
                        $json->errors  = $e->errors();
                    }
                    break;
            }

            $json->render();
        }
    }