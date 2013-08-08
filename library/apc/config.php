<?php

    class apc_config extends entity_config_defaults {

        protected function defaults() {
            return [
                'key_prefix' => null,
            ];
        }
    }