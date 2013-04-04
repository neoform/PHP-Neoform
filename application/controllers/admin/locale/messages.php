<?php

    core::locale()->set_namespace('main');

	$view = new render_view();

	$view->meta_title = 'Locale Translations';

	$keys = new locale_key_collection(null, locale_key_dao::all());

	$translations = [];
	foreach ($keys as $key) {
		$translations[$key->id] = [
			'key' 	   => htmlspecialchars($key->body),
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

	$locales = [];
	foreach (locale_dao::all() as $locale) {
		$locales[$locale['iso2']] = $locale;
	}

	$view->locales 		= $locales;
	$view->translations = $translations;

	$view->render('admin/locale/messages');
