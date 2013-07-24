<?php

    class controller_account_email extends controller_account {

        public function default_action() {
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

            $json = new render_json;

            try {
                user_api::update_email(
                    core::auth()->user(),
                    [
                        'email' => core::http()->post('email'),
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
            $view = new render_view;
            $view->meta_title = 'Change Email / Account';
            $view->subheader  = 'Change Email';
            $view->render('account/email');
        }
    }

