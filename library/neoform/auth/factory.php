<?php

    namespace neoform\auth;

    use neoform;

    class factory implements neoform\core\factory {

        public static function init(array $args) {
            if (! $args) {
                try {
                    $auth = new model(neoform\core::http()->cookie(neoform\core::config()['auth']['cookie']));
                    if ((new neoform\type\date)->getTimestamp() > $auth->expires_on->getTimestamp()) {
                        neoform\entity::dao('auth')->delete($auth);
                        $auth->reset();
                    }
                    return $auth;
                } catch (exception $e) {
                    return new model(null, []);
                }
            }
        }
    }
