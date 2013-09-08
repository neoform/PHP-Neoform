<?php

    namespace neoform;

    class sample extends config_environment {

        public function __construct() {
            $this->merge([

                'core' => new core_config([
                    'site_name' => 'Sample Site',
                ]),

                'http' => new http_config([
                    'domain' => 'localhost.local',

                    // Required subdomains - default
                    'subdomain_default' => [
                        'regular' => null,
                        'secure'  => null,
                    ],

                    'session' => [
                        // random string to make the ref code more random - you can change this, but it will
                        // kill all sessions (forms that are being filled out).
                        'ref_secret' => 'AAABBBCCCDDDEEEFFF12345678901234567890123456789012345678901234567890',

                        // Session handlers
                        'flash_cache_engine' => 'redis',

                        // Which server is used when reading
                        'flash_cache_pool_read' => 'master',

                        // Which server is used when writing
                        'flash_cache_pool_write' => 'master',
                    ],
                ]),

                'locale' => new locale_config([

                    // locale translations active
                    'active' => false,

                    // default locale
                    'default' => 'en',

                    // allowed locales
                    'allowed' => [ 'en', 'fr', 'es', 'it' ],

                    // which cache engine should be used to store compiled translation dictionaries
                    'cache_engine' => 'redis',

                    // which cache pool should translations use to store compiled translation dictionaries
                    // Which server is used when reading
                    'cache_engine_read' => 'master',

                    // Which server is used when writing
                    'cache_engine_write' => 'master',
                ]),

                'sql' => new sql_config([

                    // Which server is used when reading
                    'default_pool_read' => 'master',

                    // Which server is used when writing
                    'default_pool_write' => 'master',

                    // SQL Server connections
                    'pools' => [
                        'master' => [
                            [
                                'dsn'      => 'mysql:dbname=core;host=127.0.0.1',
                                'user'     => 'root',
                                'password' => 'root',
                            ],
                        ],
                    ],
                ]),

                // Use default auth config values
                'auth' => new auth_config,

                'memcache' => new memcache_config([
                    //leave black (empty string) if no prefix is needed
                    //this prefix is useful if you have multiple instances of the same code on the same memcache pool (maybe prod/dev on one memcache pool)
                    'key_prefix' => 'sample',

                    'default_pool' => 'master',

                    // Memcache Server connections
                    'pools' => [
                        'master' => [
                            [ 'host' => '127.0.0.1', 'port' => 11211, 'weight' => 1 ],
                        ],
                    ],
                ]),

                'redis' => new redis_config([
                    //leave black (empty string) if no prefix is needed
                    //this prefix is useful if you have multiple instances of the same code base
                    'key_prefix' => 'sample',

                    // Which server is used when reading
                    'default_pool_read' => 'master',

                    // Which server is used when writing
                    'default_pool_write' => 'master',

                    // Redis Server connections
                    'pools' => [
                        'master' => [
                            [ 'host' => '127.0.0.1', 'port' => 6379 ],
                        ],
                    ],
                ]),

                'apc' => new apc_config([
                    // Prefix key
                    'key_prefix' => 'sample',
                ]),

                'email' => new email_config([
                    // Hash secret used for generating email unsubscribe links - should be as random as possible
                    'unsubscribe_secret' => 'AAABBBCCCDDDEEEFFF12345678901234567890123456789012345678901234567890',
                ]),

                'entity' => new entity_config([

                    'defaults' => [
                        'source_engine'            => 'mysql',
                        'source_engine_pool_read'  => 'master',
                        'source_engine_pool_write' => 'master',

                        'cache_engine'            => 'memcache',
                        'cache_engine_pool_read'  => 'master',
                        'cache_engine_pool_write' => 'master',

                        'cache_meta_engine'            => 'redis',
                        'cache_meta_engine_pool_read'  => 'master',
                        'cache_meta_engine_pool_write' => 'master',
                    ],

                    'overrides' => [
                        'auth' => [
                            'source_engine'            => 'redis',
                            'source_engine_pool_read'  => 'master',
                            'source_engine_pool_write' => 'master',
                        ],
                    ],
                ]),
            ]);
        }
    }