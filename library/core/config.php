<?php

    class core_config extends entity_config_defaults {

        protected function defaults() {
            return [
                'site_name' => null,

                // When using multiple sites with a common db, user accounts are created under this site_id
                'site_id' => 1,

                // manually set the timezone [required]
                'timezone' => 'UTC',

                // page output encoding [required]
                'encoding' => 'utf-8',
            ];
        }
    }