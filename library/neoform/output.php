<?php

    namespace neoform;

    class output extends \neoform\core\singleton {

        public static function init($name) {
            return new output\model;
        }
    }