<?php

    namespace neoform;

    class controller_admin_locale extends controller_admin {

        public function default_action() {
            output::instance()->redirect('admin/locale/namespaces');
        }
    }