<?php

    core::locale()->set_namespace('main');

    $page = (int) core::http()->parameter('page');
    $per_page = 20;

    if ($page < 1) {
        $page = 1;
    }

    $view = new render_view();

    $view->meta_title = 'ACL Roles';

    $roles = new acl_role_collection(acl_role_dao::limit($per_page, 'id', 'asc', null));
    $roles->acl_resource_collection();
    $roles->acl_group_collection();

    $view->roles    = $roles;

    $view->page     = $page;
    $view->total    = acl_role_dao::count();
    $view->per_page = $per_page;

    $view->render('admin/acl/role');
