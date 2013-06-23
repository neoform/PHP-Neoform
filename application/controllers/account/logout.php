<?php

    core::http()->ref();

    //verify
    if (core::auth()->user_id) {
        try {
            auth_api::logout(core::auth());
        } catch (input_exception $e) {

        }
    }

    core::output()->redirect();
