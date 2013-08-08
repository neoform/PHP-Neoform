<?php

    class entity_config extends entity_config_defaults {

        protected function defaults() {
            return [

                'defaults' => [
                    // When no entity source engine is defined in definition file, use this engine
                    'source_engine' => null,

                    // Default source engine read connection name
                    'source_engine_pool_read' => null,

                    // Default source engine write connection name
                    'source_engine_pool_write' => null,

                    // When no entity cache engine is defined in definition file, use this engine
                    'cache_engine' => null,

                    // Default cache engine read connection name
                    'cache_engine_pool_read' => null,

                    // Default cache engine write connection name
                    'cache_engine_pool_write' => null,
                ],

                'overrides' => [],
            ];
        }
    }