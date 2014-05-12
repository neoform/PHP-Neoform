<?php

    namespace neoform;

    class auth extends core\singleton {

        public static function init($name) {
            try {
                $cookie = http::instance()->cookie(config::instance()['auth']['cookie']);
                $auth = new auth\model($cookie);
                if ((new \datetime)->getTimestamp() > $auth->expires_on->getTimestamp()) {
                    entity::dao('auth')->delete($auth);
                    $auth->reset();
                }
                return $auth;
            } catch (auth\exception $e) {
                return new auth\model(null, [
                    'hash' => !empty($cookie) ? $cookie : null,
                ]);
            }
        }
    }