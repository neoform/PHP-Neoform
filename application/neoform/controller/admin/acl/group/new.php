<?php

    namespace neoform;

    class controller_admin_acl_group_new extends controller_admin {

        public function default_action() {

            $view = new render\view;
            $view->meta_title = 'New Group';

            $view->roles = new acl\role\collection(null, entity::dao('acl\role')->all(), 'id');

            $view->render('admin/acl/group/new');
        }
    }