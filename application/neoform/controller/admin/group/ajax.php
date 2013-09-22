<?php

    namespace neoform;

    class controller_admin_group_ajax extends controller_admin {

        public function default_action() {
            output::instance()->output_type('json');
            http::instance()->ref();

            $json = new render\json;

            switch (http::instance()->slug('action')) {

                case 'insert':
                    try {
                        acl\group\api::insert(
                            http::instance()->posts()
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
                            new acl\group\model(http::instance()->parameter('id')),
                            http::instance()->posts()
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
                            new acl\group\model(http::instance()->parameter('id'))
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
                            http::instance()->posts()
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