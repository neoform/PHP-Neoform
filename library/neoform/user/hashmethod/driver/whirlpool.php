<?php

    namespace neoform\user\hashmethod\driver;

    class whirlpool extends \neoform\user\hashmethod\driver {

        public function hash($password, $salt, $cost) {
            $hash = $password . $salt;
            for ($i=0; $i < $cost; $i++) {
                $hash = hash('whirlpool', $hash, 1);
            }

            return $hash;
        }
    }