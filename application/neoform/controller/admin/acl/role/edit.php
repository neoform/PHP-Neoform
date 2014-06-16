<?php

    namespace neoform;

    class controller_admin_acl_role_edit extends controller_admin {

        public function default_action() {

            $role = new acl\role\model((int) http::instance()->parameter('id'));

            $view = new render\view;
            $view->meta_title = 'Edit Role';
            $view->role       = $role;

            $view->resources = new acl\resource\collection(null, entity::dao('acl\resource')->all(), 'id');

            $view->render('admin/acl/role/edit');
        }
    }