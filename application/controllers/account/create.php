<?php

    //if already logged in
    if (core::auth()->user_id) {
        core::output()->redirect();
    } else {

        // view variables
        $view = new render_view();

        $view->render('account/create');
    }
