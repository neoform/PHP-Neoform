<?php

    namespace neoform;

    class controller_admin_user_ajax extends controller_admin {

        public function default_action() {

            output::instance()->output_type('json');
            http::instance()->ref();

            $json = new render\json;

            switch (http::instance()->slug('action')) {

                case 'insert':
                    try {
                        user\api::insert(
                            http::instance()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'User could not be created';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update':
                    try {
                        user\api::admin_update(
                            new user\model(http::instance()->parameter('id')),
                            http::instance()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'User could not be updated';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'delete':
                    try {
                        user\api::delete(
                            new user\model(http::instance()->parameter('id'))
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'User could not be deleted';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update_password':
                    try {
                        user\api::admin_password_update(
                            new user\model(http::instance()->parameter('id')),
                            http::instance()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'User could not be updated';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update_groups':
                    try {
                        acl\group\user\api::insert(
                            http::instance()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'User group could not be linked';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update_roles':
                    try {
                        user\acl\role\api::insert(
                            http::instance()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'User ACL role could not be linked';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'random':
                    try {
                        $json->random = user\lib::generate_salt();
                        $json->status = 'good';
                    } catch (\exception $e) {
                        $json->status  = 'error';
                        $json->message = 'Could not generate random string';
                    }
                    break;
            }

            $json->render();
        }
    }