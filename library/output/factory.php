<?php

    class output_factory implements core_factory {

        public static function init(array $args) {
            return new output_instance;
        }
    }