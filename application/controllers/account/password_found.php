<?php

    if (core::auth()->user_id) {
        throw new error_exception('You are already logged in');
    } else {

        list($user, $new_password) = user_lostpassword_api::find(
            new site_model(core::config()->system['site_id']),
            core::http()->segment(3)
        );

        auth_lib::activate_session($user);

        core::output()->redirect('account/password/p:' . $new_password);
    }