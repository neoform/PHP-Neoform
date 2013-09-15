<?php

    namespace neoform;

    class controller_admin_acl_resource extends controller_admin {

        public function default_action() {

            $page = (int) http::instance()->parameter('page');
            $per_page = 20;

            if ($page < 1) {
                $page = 1;
            }

            $view = new render\view;

            $view->meta_title = 'ACL Roles';

            $resources = new acl\resource\collection(entity::dao('acl\resource')->limit([ 'id' => entity\record\dao::SORT_ASC ], ($page - 1) * $per_page, $per_page));
            $resources->acl_role_collection();
            $resources->child_acl_resource_collection();

            $view->resources = $resources;

            $view->page     = $page;
            $view->total    = entity::dao('acl\resource')->count();
            $view->per_page = $per_page;

            $view->render('admin/acl/resource');
        }
    }