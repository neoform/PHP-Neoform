<?php

    namespace neoform;

    class controller_admin_acl_role_ajax extends controller_admin {

        public function default_action() {
            output::instance()->output_type('json');
            http::instance()->ref();

            $json = new render\json;

            switch (http::instance()->slug('action')) {

                case 'insert':

                    if (! auth::instance()->user()->has_resource('admin/acl/role/create')) {
                        self::show403();
                        return;
                    }

                    try {
                        $role = acl\role\api::insert(
                            http::instance()->posts()
                        );

                        // Resources
                        $resource_ids = preg_split('`\s*,\s*`', http::instance()->post('resources'), -1, PREG_SPLIT_NO_EMPTY);
                        acl\role\resource\api::let(
                            $role,
                            new acl\resource\collection($resource_ids)
                        );

                        $json->role   = $role->export();
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->message() ? $e->message() : 'ACL role could not be created';
                        $json->errors  = $e->errors();
                    }
                    break;

                case 'update':

                    if (! auth::instance()->user()->has_resource('admin/acl/role/edit')) {
                        self::show403();
                        return;
                    }

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

                    if (! auth::instance()->user()->has_resource('admin/acl/role/delete')) {
                        self::show403();
                        return;
                    }

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

                case 'update_resources':

                    if (! auth::instance()->user()->has_resource('admin/acl/resource/edit')) {
                        self::show403();
                        return;
                    }

                    try {
                        $resource_ids = preg_split('`\s*,\s*`', http::instance()->post('resources'), -1, PREG_SPLIT_NO_EMPTY);
                        acl\role\resource\api::let(
                            new acl\role\model(http::instance()->parameter('id')),
                            new acl\resource\collection($resource_ids)
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->message() ? $e->message() : 'Role resources could not be linked';
                        $json->errors  = $e->errors();
                    }
                    break;
            }

            $json->render();
        }
    }