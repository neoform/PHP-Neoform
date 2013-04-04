<?php

    core::locale()->set_namespace('main');
    $view = new render_view();

    $view->folders = $folders = new folder_collection(folder_dao::by_user_parent(core::auth()->user_id, null));
    $folders->sort('name', 'desc');

    $view->files = $files = new file_collection(file_dao::by_user_folder(core::auth()->user_id, null));
    $files->sort('label', 'desc');

    $view->render('account/files');
