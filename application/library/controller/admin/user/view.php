<?php

    class controller_admin_user_view extends controller_admin {

        public function default_action() {
            $user = new user_model((int) core::http()->parameter('id'));

            $view = new render_view;
            $view->meta_title = 'User Info';
            $view->user = $user;

            $view->password_hashmethods = new user_hashmethod_collection(null, entity::dao('user_hashmethod')->all());
            $view->all_roles            = new acl_role_collection(null, entity::dao('acl_role')->all(), 'id');
            $view->all_groups           = new acl_group_collection(null, entity::dao('acl_group')->all(), 'id');
            $view->all_statuses         = new user_status_collection(null, entity::dao('user_status')->all(), 'id');

            $view->render('admin/user/view');
        }
    }
