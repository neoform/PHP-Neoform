<?php

    namespace neoform;

    class controller_account_ajax_dialog_login extends controller_account_ajax {

        public function default_action() {

            if (core::auth()->logged_in()) {
                $json = new render\json;
                $json->status = 'close';
                $json->render();
            } else {

                $dialog = new render\dialog('account/login');

                if ($message = core::http_flash()->get('login_message')) {
                    $dialog->message = current($message);
                    core::http_flash()->del('login_message');
                }

                $dialog
                    ->title('Login')
                    ->css([
                        'width' => '600px',
                    ])
                    ->content('body')
                    ->content('foot')
                    ->callback('afterLoad')
                    ->callback('afterShow')
                    ->render();
            }
        }
    }