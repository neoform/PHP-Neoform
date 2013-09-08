<?php

    namespace neoform;

    class controller_admin_locale_namespaces_messages extends controller_admin {

        public function default_action() {

            $view = new render_view;

            $view->meta_title = 'Locale Translations';

            $namespace = new locale_namespace_model(core::http()->parameter('id'));

            $keys = new locale_key_collection(entity::dao('locale_key')->by_namespace($namespace->id));

            $translations = [];
            foreach ($keys as $key) {
                $translations[$key->id] = [
                    'key'      => \htmlspecialchars($key->body),
                    'messages' => [],
                ];

                foreach (entity::dao('locale')->all() as $locale) {
                    if ($locale['iso2'] !== $key->locale) {
                        try {
                            $translations[$key->id]['messages'][$locale['iso2']] = \htmlspecialchars($key->message($locale['iso2'])->body);
                        } catch (\exception $e) {
                            $translations[$key->id]['messages'][$locale['iso2']] = '';
                        }
                    }
                }
            }

            $view->namespace    = $namespace;
            $view->locales      = \array_column(entity::dao('locale')->all(), 'name', 'iso2');
            $view->translations = $translations;
            $view->namespaces   = \array_column(entity::dao('locale_namespace')->all(), 'name', 'id');

            $view->render('admin/locale/messages');
        }
    }
