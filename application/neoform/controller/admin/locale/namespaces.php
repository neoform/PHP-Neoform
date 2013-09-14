<?php

    namespace neoform;

    class controller_admin_locale_namespaces extends controller_admin {

        public function default_action() {

            $view = new render\view;

            $view->meta_title = 'Locale Namespaces';
            $view->namespaces = new locale\nspace\collection(null, entity::dao('locale\nspace')->all());

            $view->render('admin/locale/namespaces');
        }
    }