<?php

    class cache_config extends config_defaults {

        protected function defaults() {
            return [
                // Source engine used by record_dao and link_dao
                'default_source_engine'            => null,
                'default_source_engine_pool_read'  => null,
                'default_source_engine_pool_write' => null,

                // Cache engine used by record_dao and link_dao
                'default_cache_engine'             => null,
                'default_cache_engine_pool_read'   => null,
                'default_cache_engine_pool_write'  => null,
            ];
        }
    }