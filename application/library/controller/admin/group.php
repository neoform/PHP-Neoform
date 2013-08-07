<?php

    class controller_admin_group extends controller_admin {

        public function default_action() {

            $page = (int) core::http()->parameter('page');
            $per_page = 10;

            if ($page < 1) {
                $page = 1;
            }

            $view = new render_view;

            $view->meta_title = 'Groups';

            //$users = new user_collection(entity_dao::get('user')->limit(20, 'id', 'asc', null));
            $groups = new acl_group_collection(entity_dao::get('acl_group')->pagination('id', 'asc', ($page - 1) * $per_page, $per_page));
            $roles  = $groups->acl_role_collection();
            $roles->acl_resource_collection();

            $view->groups    = $groups;

            $view->page     = $page;
            $view->total    = entity_dao::get('acl_group')->count();
            $view->per_page = $per_page;

            $view->render('admin/group');
        }
    }