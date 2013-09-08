<?php

    namespace neoform;

    class user_hashmethod_driver_whirlpool extends user_hashmethod_driver {

        public function hash($password, $salt, $cost) {
            $hash = $password . $salt;
            for ($i=0; $i < $cost; $i++) {
                $hash = \hash('whirlpool', $hash, 1);
            }

            return $hash;
        }
    }