<?php

    if (core::auth()->user_id) {
        new account_change_password();
    } else {
        throw new redirect_login_exception('account/password');
    }

    class account_change_password {

        public function __construct() {

            switch (core::http()->segment(3)) {
                case 'update':
                    $this->update();
                    break;

                default:
                    $this->display();
            }
        }

        protected function update() {

            core::http()->ref();

            $json = new render_json();

            try {
                user_api::update_password(
                    core::auth()->user(),
                    [
                        'current_password' => core::http()->post('current_password'),
                        'password1'        => core::http()->post('password1'),
                        'password2'        => core::http()->post('password2'),
                    ]
                );
                $json->status = 'good';
            } catch (input_exception $e) {
                $json->errors = $e->errors();
                $json->message = $e->message();
            }

            $json->render();
        }

        protected function display() {

            //display time
            $view = new render_view();

            if (core::http()->parameter('p')) {
                $view->reset = true;
                $view->forgotten = core::http()->parameter('p');
            }

            $view->meta_title       = 'Change Password / Account';
            $view->subheader       = 'Change Password';
            $view->social_inactive = true;

            $view->render('account/password');
        }
    }
