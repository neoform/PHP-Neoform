<?php

    class controller_account_ajax_dialog_create extends controller_account_ajax {

        public function default_action() {

            if (core::auth()->logged_in()) {
                $json = new render_json();
                $json->status = 'close';
                $json->render();
            } else {
                (new render_dialog('account/create'))
                    ->title('Create Account')
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