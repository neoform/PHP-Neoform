<?php

    if (core::auth()->user_id) {
        core::output()->redirect();
    } else {
        $view = new render_view();
        $view->render('account/password_lost');
    }
