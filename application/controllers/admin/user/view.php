<?php

    core::locale()->set_namespace('main');

    $user = new user_model((int) core::http()->parameter('id'));

    $view = new render_view();
    $view->meta_title = 'User Info';
    $view->user = $user;

    $view->password_hashmethods = new user_hashmethod_collection(null, user_hashmethod_dao::all());
    $view->all_permissions      = new permission_collection(null, permission_dao::all());
    $view->all_statuses         = new user_status_collection(null, user_status_dao::all());

    $view->render('admin/user/view');
