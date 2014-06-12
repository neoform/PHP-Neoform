<?php

    namespace neoform;

    class controller_admin_acl_resource_new extends controller_admin {

        public function default_action() {

            $view = new render\view;
            $view->meta_title = 'New Resource';

            if ($resource_id = (int) http::instance()->parameter('id')) {
                $view->resource = new acl\resource\model($resource_id);
            }

            $view->roles = new acl\role\collection(null, entity::dao('acl\role')->all(), 'id');

            $view->render('admin/acl/resource/new');
        }
    }