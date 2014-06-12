<?php

    namespace neoform;

    class controller_admin_acl_group_ajax extends controller_admin {

        public function default_action() {
            output::instance()->output_type('json');
            http::instance()->ref();

            $json = new render\json;

            switch (http::instance()->slug('action')) {

                case 'insert':

                    if (! auth::instance()->user()->has_resource('admin/acl/group/create')) {
                        self::show403();
                        return;
                    }

                    try {
                        $group = acl\group\api::insert(
                            http::instance()->posts()
                        );

                        // Roles
                        $role_ids = preg_split('`\s*,\s*`', http::instance()->post('roles'), -1, PREG_SPLIT_NO_EMPTY);
                        acl\group\role\api::let(
                            $group,
                            new acl\role\collection($role_ids)
                        );

                        $json->status = 'good';
                        $json->group = $group->export();

                    } catch (input\exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->message() ?: 'Group could not be created';
                        $json->errors  = $e->errors();
                    }
                    break;

                case 'update':

                    if (! auth::instance()->user()->has_resource('admin/acl/group/edit')) {
                        self::show403();
                        return;
                    }

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

                    if (! auth::instance()->user()->has_resource('admin/acl/group/delete')) {
                        self::show403();
                        return;
                    }

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

                    if (! auth::instance()->user()->has_resource('admin/acl/role/edit')) {
                        self::show403();
                        return;
                    }

                    try {
                        $role_ids = preg_split('`\s*,\s*`', http::instance()->post('roles'), -1, PREG_SPLIT_NO_EMPTY);
                        acl\group\role\api::let(
                            new acl\group\model(http::instance()->parameter('id')),
                            new acl\role\collection($role_ids)
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->message() ? $e->message() : 'Group roles could not be linked';
                        $json->errors  = $e->errors();
                    }
                    break;
            }

            $json->render();
        }
    }
