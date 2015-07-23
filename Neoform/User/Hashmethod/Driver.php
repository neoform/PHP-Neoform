<?php

    namespace Neoform\User\Hashmethod;

    abstract class Driver {

        /**
         * Hashes a password, with salt given a certain cost value
         *
         * @param string        $password
         * @param binary|string $salt
         * @param integer       $cost
         *
         * @return binary|string
         */
        abstract public function hash($password, $salt, $cost);
    }