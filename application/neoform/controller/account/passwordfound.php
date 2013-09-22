<?php

    namespace neoform;

    class controller_account_passwordfound extends controller_index {

        public function default_action() {

            if (auth::instance()->logged_in()) {
                output::instance()->redirect(http\flash::instance()->get('login_bounce'));
            } else {
                list($user, $new_password) = user\lostpassword\api::find(
                    new site\model(config::instance()['core']['site_id']),
                    http::instance()->slug('code')
                );

                auth\lib::activate_session($user);

                output::instance()->redirect("account/password/p:{$new_password}");
            }
        }
    }