<?php

    namespace neoform;

    class controller_admin_group_ajax extends controller_admin {

        public function default_action() {
            core::output()->output_type('json');
            core::http()->ref();

            $json = new render\json;

            switch (core::http()->segment('action')) {

                case 'insert':
                    try {
                        acl\group\api::insert(
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->message() ?: 'Group could not be created';
                        $json->errors  = $e->errors();
                    }
                    break;

                case 'update':
                    try {
                        acl\group\api::update(
                            new acl\group\model(core::http()->parameter('id')),
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->message() ?: 'Group could not be updated';
                        $json->errors  = $e->errors();
                    } catch (acl\group\exception $e) {
                        core::debug($e);
                    }
                    break;

                case 'delete':
                    try {
                        acl\group\api::delete(
                            new acl\group\model(core::http()->parameter('id'))
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->message() ?: 'Group could not be deleted';
                        $json->errors  = $e->errors();
                    }
                    break;

                case 'update_roles':
                    try {
                        user\acl\role\api::insert(
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->message() ? $e->message() : 'User ACL role could not be linked';
                        $json->errors  = $e->errors();
                    }
                    break;
            }

            $json->render();
        }
    }