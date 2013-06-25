<?php

    core::locale()->set_namespace('admin');

    $view = new render_view();

    $view->meta_title = 'Locale Translations';

    $namespace = new locale_namespace_model(core::http()->parameter('id'));

    $keys = new locale_key_collection(locale_key_dao::by_namespace($namespace->id));

    $translations = [];
    foreach ($keys as $key) {
        $translations[$key->id] = [
            'key'      => htmlspecialchars($key->body),
            'messages' => [],
        ];

        foreach (locale_dao::all() as $locale) {
            if ($locale['iso2'] !== $key->locale) {
                try {
                    $translations[$key->id]['messages'][$locale['iso2']] = htmlspecialchars($key->message($locale['iso2'])->body);
                } catch (exception $e) {
                    $translations[$key->id]['messages'][$locale['iso2']] = '';
                }
            }
        }
    }

    $view->locales      = array_column(locale_dao::all(), 'name', 'iso2');
    $view->translations = $translations;
    $view->namespaces   = array_column(locale_namespace_dao::all(), 'name', 'id');

    $view->render('admin/locale/messages');
