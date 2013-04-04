<?php

    core::output()->output_type('json');

    if (core::auth()->user_id) {
        throw new error_exception('You are already logged in');
    } else {

        core::http()->ref();

        $json = new render_json();

        try {
            user_lostpassword_api::lost(
                new site_model(core::config()->system['site_id']),
                core::http()->posts()
            );
            $json->status = 'good';
        } catch (input_exception $e) {
            $json->message = $e->message();
            $json->errors  = $e->errors();
        }
        $json->render();
    }
