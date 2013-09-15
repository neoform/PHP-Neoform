<?php

    namespace neoform;

    class auth extends core\singleton {

        public static function init($name) {
            try {
                $auth = new auth\model(http::instance()->cookie(config::instance()['auth']['cookie']));
                if ((new \datetime)->getTimestamp() > $auth->expires_on->getTimestamp()) {
                    entity::dao('auth')->delete($auth);
                    $auth->reset();
                }
                return $auth;
            } catch (auth\exception $e) {
                return new auth\model(null, []);
            }
        }
    }