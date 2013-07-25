<?php

    class controller_admin_group_ajax extends controller_admin {

        public function default_action() {
            core::output()->output_type('json');
            core::http()->ref();

            $json = new render_json;

            switch (core::http()->segment('action')) {

                case 'insert':
                    try {
                        acl_group_api::insert(
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->message() ?: 'Group could not be created';
                        $json->errors  = $e->errors();
                    }
                    break;

                case 'update':
                    try {
                        acl_group_api::update(
                            new acl_group_model(core::http()->parameter('id')),
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->message() ?: 'Group could not be updated';
                        $json->errors  = $e->errors();
                    } catch (acl_group_exception $e) {
                        core::debug($e);
                    }
                    break;

                case 'delete':
                    try {
                        acl_group_api::delete(
                            new acl_group_model(core::http()->parameter('id'))
                        );
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->message() ?: 'Group could not be deleted';
                        $json->errors  = $e->errors();
                    }
                    break;

                case 'update_roles':
                    try {
                        user_acl_role_api::insert(
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->message() ? $e->message() : 'User ACL role could not be linked';
                        $json->errors  = $e->errors();
                    }
                    break;
            }

            $json->render();
        }
    }