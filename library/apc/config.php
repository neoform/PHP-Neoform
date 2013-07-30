<?php

    class apc_config extends config_defaults {

        protected function defaults() {
            return [
                'key_prefix' => null,
            ];
        }
    }