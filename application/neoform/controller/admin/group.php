<?php

    namespace neoform;

    class controller_admin_group extends controller_admin {

        public function default_action() {

            $page = (int) core::http()->parameter('page');
            $per_page = 10;

            if ($page < 1) {
                $page = 1;
            }

            $view = new render\view;

            $view->meta_title = 'Groups';

            //$users = new user_collection(entity::dao('user')->limit(20, 'id', 'asc', null));
            $groups = acl\group\collection::limit([ 'id' => entity\record\dao::SORT_ASC ], ($page - 1) * $per_page, $per_page);
            $roles  = $groups->acl_role_collection();
            $roles->acl_resource_collection();

            $view->groups   = $groups;

            $view->page     = $page;
            $view->total    = entity::dao('acl\group')->count();
            $view->per_page = $per_page;

            $view->render('admin/group');
        }
    }