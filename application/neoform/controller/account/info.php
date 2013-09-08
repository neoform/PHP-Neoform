<?php

    namespace neoform;

    class controller_account_info extends controller_account {

        public function default_action() {
            //display time
            $view = new render_view;

            $view->meta_title = 'Account Info / Account';
            $view->subheader  = 'Account Information';

            $view->render('account/info');
        }
    }
