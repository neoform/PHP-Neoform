<?php

    namespace neoform;

    class controller_admin_acl_role extends controller_admin {

        public function default_action() {

            $page = (int) http::instance()->parameter('page');
            $per_page = 20;

            if ($page < 1) {
                $page = 1;
            }

            $view = new render\view;

            $view->meta_title = 'ACL Roles';

            $roles = new acl\role\collection(entity::dao('acl\role')->limit([ 'id' => entity\record\dao::SORT_ASC ], ($page - 1) * $per_page, $per_page));
            $roles->acl_resource_collection();
            $roles->acl_group_collection();

            $view->roles    = $roles;

            $view->page     = $page;
            $view->total    = entity::dao('acl\role')->count();
            $view->per_page = $per_page;

            $view->render('admin/acl/role');
        }
    }