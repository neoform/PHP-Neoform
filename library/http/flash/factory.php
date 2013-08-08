<?php

    class http_flash_factory implements core_factory {

        public static function init(array $args) {
            return new http_flash_instance(core::config()['http']['session']);
        }
    }