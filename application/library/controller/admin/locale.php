<?php

    class controller_admin_locale extends controller_admin {

        public function default_action() {
            core::output()->redirect('admin/locale/namespaces');
        }
    }