<?php

    namespace neoform;

    class controller_admin_acl_resource_ajax extends controller_admin {

        public function default_action() {
            core::output()->output_type('json');
            core::http()->ref();

            $json = new render_json;

            switch (core::http()->segment('action')) {

                case 'insert':
                    try {
                        acl_resource_api::insert(
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'ACL resource could not be created';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update':
                    try {
                        acl_resource_api::update(
                            new acl_resource_model(core::http()->parameter('id')),
                            core::http()->posts()
                        );
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'ACL resource could not be updated';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'delete':
                    try {
                        acl_resource_api::delete(new acl_resource_model(core::http()->parameter('id')));
                        $json->status = 'good';
                    } catch (input_exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'ACL resource could not be deleted';
                        $json->errors = $e->errors();
                    }
                    break;
            }

            $json->render();
        }
    }