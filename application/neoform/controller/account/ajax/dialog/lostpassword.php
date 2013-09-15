<?php

    namespace neoform;

    class controller_account_ajax_dialog_lostpassword extends controller_account_ajax {

        public function default_action() {
            if (auth::instance()->logged_in()) {
                $json = new render\json;
                $json->status = 'close';
                $json->render();
            } else {
                (new render\dialog('account/lostpassword'))
                    ->title('Lost Password')
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