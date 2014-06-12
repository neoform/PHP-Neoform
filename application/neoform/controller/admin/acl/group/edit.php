<?php

    namespace neoform;

    class controller_admin_acl_group_edit extends controller_admin {

        public function default_action() {

            $group = new acl\group\model((int) http::instance()->parameter('id'));

            $view = new render\view;
            $view->meta_title = 'Edit Group';
            $view->group      = $group;

            $view->roles = new acl\role\collection(null, entity::dao('acl\role')->all(), 'id');

            $view->render('admin/acl/group/edit');
        }
    }