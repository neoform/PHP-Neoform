<?php

    namespace neoform;

    class controller_admin_group_view extends controller_admin {

        public function default_action() {

            $group = new acl\group\model((int) core::http()->parameter('id'));

            $view = new render\view;
            $view->meta_title = 'Group';
            $view->group      = $group;

            $view->roles = new acl\role\collection(null, entity::dao('acl\role')->all(), 'id');

            $view->render('admin/group/view');
        }
    }