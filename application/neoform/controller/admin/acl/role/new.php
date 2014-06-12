<?php

    namespace neoform;

    class controller_admin_acl_role_new extends controller_admin {

        public function default_action() {

            $view = new render\view;
            $view->meta_title = 'New Role';

            $view->resources = new acl\resource\collection(null, entity::dao('acl\resource')->all(), 'id');

            $view->render('admin/acl/role/new');
        }
    }