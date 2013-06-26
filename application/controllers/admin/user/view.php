<?php

    core::locale()->set_namespace('admin');

    $user = new user_model((int) core::http()->parameter('id'));

    $view = new render_view();
    $view->meta_title = 'User Info';
    $view->user = $user;

    $view->password_hashmethods = new user_hashmethod_collection(null, user_hashmethod_dao::all());
    $view->all_roles            = new acl_role_collection(null, acl_role_dao::all(), 'id');
    $view->all_groups           = new acl_group_collection(null, acl_group_dao::all(), 'id');
    $view->all_statuses         = new user_status_collection(null, user_status_dao::all(), 'id');

    $view->render('admin/user/view');
