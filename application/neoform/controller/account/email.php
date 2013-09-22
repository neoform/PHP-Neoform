<?php

    namespace neoform;

    class controller_account_email extends controller_account {

        public function default_action() {
            switch (http::instance()->slug('action')) {
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
                user\api::update_email(
                    auth::instance()->user(),
                    [
                        'email' => http::instance()->post('email'),
                    ]
                );
                $json->status = 'good';
            } catch (input\exception $e) {
                $json->errors = $e->errors();
                $json->message = $e->message();
            }

            $json->render();
        }

        protected function display() {
            $view = new render\view;
            $view->meta_title = 'Change Email / Account';
            $view->subheader  = 'Change Email';
            $view->render('account/email');
        }
    }

