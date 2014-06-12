<?php

    namespace neoform;

    class controller_admin_acl_resource_ajax extends controller_admin {

        public function default_action() {
            output::instance()->output_type('json');
            http::instance()->ref();

            $json = new render\json;

            switch (http::instance()->slug('action')) {

                case 'insert':

                    if (! auth::instance()->user()->has_resource('admin/acl/resource/create')) {
                        self::show403();
                        return;
                    }

                    try {
                        $resource = acl\resource\api::insert(
                            http::instance()->posts()
                        );

                        // Roles
                        $role_ids = preg_split('`\s*,\s*`', http::instance()->post('roles'), -1, PREG_SPLIT_NO_EMPTY);
                        acl\role\resource\api::let_resource(
                            $resource,
                            new acl\role\collection($role_ids)
                        );

                        $json->status = 'good';
                        $json->resource = $resource->export();
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'ACL resource could not be created';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'update':

                    if (! auth::instance()->user()->has_resource('admin/acl/resource/edit')) {
                        self::show403();
                        return;
                    }

                    try {
                        acl\resource\api::update(
                            new acl\resource\model(http::instance()->parameter('id')),
                            http::instance()->posts()
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'ACL resource could not be updated';
                        $json->errors = $e->errors();
                    }
                    break;

                case 'move':

                    if (! auth::instance()->user()->has_resource('admin/acl/resource/edit')) {
                        self::show403();
                        return;
                    }

                    $result = acl\resource\api::move(
                        new acl\resource\model((int) http::instance()->parameter('id')),
                        http::instance()->post('parent_id')
                    );

                    if ($result instanceof acl\resource\model) {
                        $json->status = 'good';
                    } else {
                        $json->status  = 'error';
                        $json->message = is_string($result) ? $result : 'Category could not be updated';
                    }
                    break;

                case 'delete':

                    if (! auth::instance()->user()->has_resource('admin/acl/resource/delete')) {
                        self::show403();
                        return;
                    }

                    try {
                        acl\resource\api::delete(new acl\resource\model(http::instance()->parameter('id')));
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status = 'error';
                        $json->message = $e->message() ? $e->message() : 'ACL resource could not be deleted';
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
                        acl\role\resource\api::let_resource(
                            new acl\resource\model(http::instance()->parameter('id')),
                            new acl\role\collection($role_ids)
                        );
                        $json->status = 'good';
                    } catch (input\exception $e) {
                        $json->status  = 'error';
                        $json->message = $e->message() ? $e->message() : 'Role resources could not be linked';
                        $json->errors  = $e->errors();
                    }
                    break;

                case 'children':
                    $order_by = [ 'name' => entity\dao::SORT_ASC, ];

                    if ($id = (int) http::instance()->parameter('id')) {
                        $resource = new acl\resource\model($id);
                        $json->children = $resource->child_acl_resource_collection($order_by)->field('name', 'id');
                    }

                    $json->status = 'good';
                    break;
            }

            $json->render();
        }
    }