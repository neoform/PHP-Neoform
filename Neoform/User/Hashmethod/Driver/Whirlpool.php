<?php

    namespace Neoform\User\Hashmethod\Driver;

    use Neoform;

    class Whirlpool extends Neoform\User\Hashmethod\Driver {

        public function hash($password, $salt, $cost) {
            $hash = $password . $salt;
            for ($i=0; $i < $cost; $i++) {
                $hash = hash('whirlpool', $hash, 1);
            }

            return $hash;
        }
    }