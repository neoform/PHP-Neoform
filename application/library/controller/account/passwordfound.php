<?php

    class controller_account_passwordfound extends controller_index {

        public function default_action() {

            if (core::auth()->logged_in()) {
                core::output()->redirect(core::flash()->get('login_bounce'));
            } else {
                list($user, $new_password) = user_lostpassword_api::find(
                    new site_model(core::config()->system['site_id']),
                    core::http()->segment('code')
                );

                auth_lib::activate_session($user);

                core::output()->redirect("account/password/p:{$new_password}");
            }
        }
    }