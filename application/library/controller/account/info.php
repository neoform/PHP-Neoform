<?php

    class controller_account_info extends controller_account {

        public function default_action() {
            //display time
            $view = new render_view;

            $view->meta_title      = 'Account Info / Account';
            $view->subheader       = 'Account Information';
            $view->social_inactive = true;

            $view->contact = $contact = core::auth()->contact();
            $view->address = $address = $contact->address();

            $view->provinces = new geo_province_collection(null, entity_dao::get('geo_province')->all());

            if ($address && $address->province_id) {
                $view->cities = entity_dao::get('geo_city')->by_province_full(new geo_province_model($address->province_id));
            }

            $view->render('account/info');
        }
    }
