<?php

    if (core::auth()->user_id) {

        $view = new render_view();

        $view->meta_title = 'Account Dashboard';

        $view->render('account');
    } else {
        throw new redirect_login_exception('account');
    }

