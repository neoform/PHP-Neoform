<?php

    namespace neoform;

    class controller_admin_user_ajax extends controller_admin {

        public function default_action() {

            output::instance()->output_type('json');
            http::instance()->ref();

            $json = new render\json;

            switch (http::instance()->slug('action')) {

                case 'insert':

                    if (! auth::instance()->user()->has_resource('admin/user/create')) {
                        self::show403();
                        return;
                    }

                    try {
                        $http = http::instance();

                        // New user
                        $user = user\api::admin_insert($http->posts());

                        // Sites
                        $site_ids = preg_split('`\s*,\s*`', http::instance()->post('sites'), -1, PREG_SPLIT_NO_EMPTY);
                        if ($site_ids && is_array($site_ids)) {
                            user\site\api::let(
                                $user,
                                new site\collection($site_ids)
                            );
                        }

                        // Roles
                        $role_ids = preg_split('`\s*,\s*`', http::instance()->post('roles'), -1, PREG_SPLIT_NO_EMPTY);
                        if ($role_ids && is_array($role_ids)) {
                            user\acl\role\api::let(
                                $user,
                                new acl\role\collection($role_ids)
                            );
                        }

                        // Groups
                        $group_ids = preg_split('`\s*,\s*`', http::instance()->post('groups'), -1, PREG_SPLIT_NO_EMPTY);
                        if ($group_ids && is_array($group_ids)) {
                            acl\group\user\api::let(
                                $user,
                                new acl\group\collection($group_ids)
                            );
                        }

                        $json->status = 'good';
                        $json->user   = $user->export(['id', 'email', 'status_id', ]);

                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'User could not be created';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update':

                    if (! auth::instance()->user()->has_resource('admin/user/edit')) {
                        self::show403();
                        return;
                    }

                    try {
                        $user = new user\model(http::instance()->parameter('id'));

                        user\api::admin_update(
                            $user,
                            http::instance()->posts()
                        );

                        // Sites
                        user\site\api::let(
                            $user,
                            new site\collection(preg_split('`\s*,\s*`', http::instance()->post('sites'), -1, PREG_SPLIT_NO_EMPTY))
                        );

                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'User could not be updated';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'delete':

                    if (! auth::instance()->user()->has_resource('admin/user/delete')) {
                        self::show403();
                        return;
                    }

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

                    if (! auth::instance()->user()->has_resource('admin/user/edit')) {
                        self::show403();
                        return;
                    }

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

                    if (! auth::instance()->user()->has_resource('admin/acl/group/edit')) {
                        self::show403();
                        return;
                    }

                    try {
                        $group_ids = preg_split('`\s*,\s*`', http::instance()->post('groups'), -1, PREG_SPLIT_NO_EMPTY);
                        acl\group\user\api::let(
                            new user\model(http::instance()->parameter('id')),
                            new acl\group\collection($group_ids)
                        );

                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ?: 'User group could not be linked';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update_roles':

                    if (! auth::instance()->user()->has_resource('admin/acl/role/edit')) {
                        self::show403();
                        return;
                    }

                    try {
                        $role_ids = preg_split('`\s*,\s*`', http::instance()->post('roles'), -1, PREG_SPLIT_NO_EMPTY);
                        user\acl\role\api::let(
                            new user\model(http::instance()->parameter('id')),
                            new acl\role\collection($role_ids)
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