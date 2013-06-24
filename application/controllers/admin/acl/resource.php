<?php

    core::locale()->set_namespace('main');

    $page = (int) core::http()->parameter('page');
    $per_page = 20;

    if ($page < 1) {
        $page = 1;
    }

    $view = new render_view();

    $view->meta_title = 'ACL Roles';

    $resources = new acl_resource_collection(acl_resource_dao::limit($per_page, 'id', 'asc', null));
    $resources->acl_role_collection();
    $resources->child_acl_resource_collection();

    $view->resources    = $resources;

    $view->page     = $page;
    $view->total    = acl_resource_dao::count();
    $view->per_page = $per_page;

    $view->render('admin/acl/resource');
