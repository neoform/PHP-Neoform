<?php

    namespace neoform;

    class controller_account_ajax_dialog_create extends controller_account_ajax {

        public function default_action() {

            if (core::auth()->logged_in()) {
                $json = new render\json;
                $json->status = 'close';

                if ($bounce = core::http_flash()->get('login_bounce')) {
                    $json->bounce = current($bounce);
                    core::http_flash()->del('login_bounce');
                }

                $json->render();
            } else {
                (new render\dialog('account/create'))
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