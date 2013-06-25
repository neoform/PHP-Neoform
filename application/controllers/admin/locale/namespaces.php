<?php

    core::locale()->set_namespace('admin');

    $view = new render_view();

    $view->meta_title = 'Locale Namespaces';
    $view->namespaces   = new locale_namespace_collection(null, locale_namespace_dao::all());

    $view->render('admin/locale/namespaces');
