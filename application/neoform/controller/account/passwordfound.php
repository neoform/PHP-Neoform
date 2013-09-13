<?php

    namespace neoform;

    class controller_account_passwordfound extends controller_index {

        public function default_action() {

            if (core::auth()->logged_in()) {
                core::output()->redirect(core::http_flash()->get('login_bounce'));
            } else {
                list($user, $new_password) = user\lostpassword\api::find(
                    new site\model(core::config()['core']['site_id']),
                    core::http()->segment('code')
                );

                auth\lib::activate_session($user);

                core::output()->redirect("account/password/p:{$new_password}");
            }
        }
    }