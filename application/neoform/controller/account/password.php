<?php

    namespace neoform;

    class controller_account_password extends controller_account {

        public function default_action() {

            switch (http::instance()->segment('action')) {
                case 'update':
                    $this->update();
                    break;

                default:
                    $this->display();
            }
        }

        protected function update() {

            http::instance()->ref();

            $json = new render\json;

            try {
                user\api::update_password(
                    auth::instance()->user(),
                    [
                        'current_password' => http::instance()->post('current_password'),
                        'password1'        => http::instance()->post('password1'),
                        'password2'        => http::instance()->post('password2'),
                    ]
                );
                $json->status = 'good';
            } catch (input\exception $e) {
                $json->errors  = $e->errors();
                $json->message = $e->message();
            }

            $json->render();
        }

        protected function display() {

            //display time
            $view = new render\view;

            if (http::instance()->parameter('p')) {
                $view->reset     = true;
                $view->forgotten = http::instance()->parameter('p');
            }

            $view->meta_title      = 'Change Password / Account';
            $view->subheader       = 'Change Password';
            $view->social_inactive = true;

            $view->render('account/password');
        }
    }
