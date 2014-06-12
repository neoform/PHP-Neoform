<?php

    namespace neoform;

    class controller_admin_acl_resource_view extends controller_admin {

        public function default_action() {

            $resource = new acl\resource\model((int) http::instance()->parameter('id'));

            $view = new render\view;
            $view->meta_title = 'View Resource';
            $view->resource   = $resource;

            $view->roles = new acl\role\collection(null, entity::dao('acl\role')->all(), 'id');

            $view->render('admin/acl/resource/view');
        }
    }