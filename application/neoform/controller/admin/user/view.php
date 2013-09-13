<?php

    namespace neoform;

    class controller_admin_user_view extends controller_admin {

        public function default_action() {
            $user = new user\model((int) core::http()->parameter('id'));

            $view = new render\view;
            $view->meta_title = 'User Info';
            $view->user = $user;

            $view->password_hashmethods = new user\hashmethod\collection(null, entity::dao('user\hashmethod')->all());
            $view->all_roles            = new acl\role\collection(null, entity::dao('acl\role')->all(), 'id');
            $view->all_groups           = new acl\group\collection(null, entity::dao('acl\group')->all(), 'id');
            $view->all_statuses         = new user\status\collection(null, entity::dao('user\status')->all(), 'id');

            $view->render('admin/user/view');
        }
    }
