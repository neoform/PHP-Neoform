<?php

    namespace neoform;

    class controller_admin_acl_role_ajax extends controller_admin {

        public function default_action() {
            core::output()->output_type('json');
            core::http()->ref();

            $json = new render\json;

            switch (core::http()->segment('action')) {

                case 'insert':
                    try {
                        acl\role\api::insert(
                            core::http()->posts()
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
                            new acl\role\model(core::http()->parameter('id')),
                            core::http()->posts()
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
                            new acl\role\model(core::http()->parameter('id'))
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