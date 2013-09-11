<?php

    namespace neoform\auth;

    use neoform;

    class factory implements neoform\core\factory {

        public static function init(array $args) {
            if (! $args) {
                try {
                    $auth = new neoform\auth\model(neoform\core::http()->cookie(neoform\core::config()['auth']['cookie']));
                    if ((new neoform\type\date)->getTimestamp() > $auth->expires_on->getTimestamp()) {
                        neoform\entity::dao('auth')->delete($auth);
                        $auth->reset();
                    }
                    return $auth;
                } catch (neoform\auth\exception $e) {
                    return new neoform\auth\model(null, []);
                }
            }
        }
    }
