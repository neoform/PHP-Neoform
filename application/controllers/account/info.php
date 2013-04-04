<?php

    if (core::auth()->user_id) {
        //display time
        $view = new render_view();

        $view->meta_title       = 'Account Info / Account';
        $view->subheader        = 'Account Information';
        $view->social_inactive  = true;

        $view->contact  = $contact = core::auth()->contact();
        $view->address    = $address = $contact->address();

        $view->provinces = new geo_province_collection(null, geo_province_dao::all());

        if ($address && $address->province_id) {
            $view->cities = geo_city_dao::by_province_full(new geo_province_model($address->province_id));
        }

        $view->render('account/info');
    } else {
        throw new redirect_login_exception('account/info');
    }

