<?php

    namespace neoform;

    class controller_admin_locale_namespaces_messages extends controller_admin {

        public function default_action() {

            $view = new render\view;

            $view->meta_title = 'Locale Translations';

            $namespace = new locale\nspace\model(http::instance()->parameter('id'));

            $keys = new locale\key\collection(entity::dao('locale\key')->by_namespace($namespace->id));

            $translations = [];
            foreach ($keys as $key) {
                $translations[$key->id] = [
                    'key'      => htmlspecialchars($key->body),
                    'messages' => [],
                ];

                foreach (entity::dao('locale')->all() as $locale) {
                    if ($locale['iso2'] !== $key->locale) {
                        try {
                            $translations[$key->id]['messages'][$locale['iso2']] = htmlspecialchars($key->message($locale['iso2'])->body);
                        } catch (\exception $e) {
                            $translations[$key->id]['messages'][$locale['iso2']] = '';
                        }
                    }
                }
            }

            $view->namespace    = $namespace;
            $view->locales      = array_column(entity::dao('locale')->all(), 'name', 'iso2');
            $view->translations = $translations;
            $view->namespaces   = array_column(entity::dao('locale\nspace')->all(), 'name', 'id');

            $view->render('admin/locale/messages');
        }
    }
