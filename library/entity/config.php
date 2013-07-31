<?php

    class entity_config extends config_defaults {

        protected function defaults() {
            return [
                // When no entity source engine is defined in definition file, use this engine
                'default_source_engine' => null,

                // Default source engine read connection name
                'default_source_engine_pool_read' => null,

                // Default source engine write connection name
                'default_source_engine_pool_write' => null,

                // When no entity cache engine is defined in definition file, use this engine
                'default_cache_engine' => null,

                // Default cache engine read connection name
                'default_cache_engine_pool_read' => null,

                // Default cache engine write connection name
                'default_cache_engine_pool_write' => null,
            ];
        }
    }