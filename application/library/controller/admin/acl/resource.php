<?php

    class controller_admin_acl_resource extends controller_admin {

        public function default_action() {

            $page = (int) core::http()->parameter('page');
            $per_page = 20;

            if ($page < 1) {
                $page = 1;
            }

            $view = new render_view;

            $view->meta_title = 'ACL Roles';

            $resources = new acl_resource_collection(entity_dao::get('acl_resource')->limit($per_page, 'id', 'asc', null));
            $resources->acl_role_collection();
            $resources->child_acl_resource_collection();

            $view->resources    = $resources;

            $view->page     = $page;
            $view->total    = entity_dao::get('acl_resource')->count();
            $view->per_page = $per_page;

            $view->render('admin/acl/resource');
        }
    }