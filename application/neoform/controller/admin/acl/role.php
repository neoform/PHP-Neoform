<?php

    namespace neoform;

    class controller_admin_acl_role extends controller_admin {

        public function default_action() {

            $page = (int) core::http()->parameter('page');
            $per_page = 20;

            if ($page < 1) {
                $page = 1;
            }

            $view = new render_view;

            $view->meta_title = 'ACL Roles';

            $roles = new acl_role_collection(entity::dao('acl_role')->limit([ 'id' => entity_record_dao::SORT_ASC ], ($page - 1) * $per_page, $per_page));
            $roles->acl_resource_collection();
            $roles->acl_group_collection();

            $view->roles    = $roles;

            $view->page     = $page;
            $view->total    = entity::dao('acl_role')->count();
            $view->per_page = $per_page;

            $view->render('admin/acl/role');
        }
    }