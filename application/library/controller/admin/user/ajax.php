<?php

    class controller_admin_user_ajax extends controller_admin {

        public function default_action() {

            core::output()->output_type('json');
            core::http()->ref();

            $json = new render_json;

            switch (core::http()->segment(4)) {

                case 'insert':
                    try {
                        user_api::insert(
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'User could not be created';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update':
                    try {
                        user_api::admin_update(
                            new user_model(core::http()->parameter('id')),
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'User could not be updated';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'delete':
                    try {
                        user_api::delete(
                            new user_model(core::http()->parameter('id'))
                        );
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'User could not be deleted';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update_password':
                    try {
                        user_api::admin_password_update(
                            new user_model(core::http()->parameter('id')),
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'User could not be updated';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update_groups':
                    try {
                        acl_group_user_api::insert(
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'User group could not be linked';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update_roles':
                    try {
                        user_acl_role_api::insert(
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'User ACL role could not be linked';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'random':
                    try {
                        $json->random = user_lib::generate_salt();
                        $json->status = 'good';
                    } catch (exception $e) {
                        $json->status  = 'error';
                        $json->message = 'Could not generate random string';
                    }
                    break;
            }

            $json->render();
        }
    }