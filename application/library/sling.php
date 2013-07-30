<?php

    class sling extends config_environment {

        public function __construct() {
            $this->merge([
                'core' => new core_config([
                    'site_name' => 'PHP Neoform',
                ]),

                'http' => new http_config([
                    'domain' => 'city17.ca',

                    'subdomain_default' => [
                        'regular' => 'phpcore',
                        'secure'  => 'phpcore',
                    ],

                    'session' => [
                        // random string to make the ref code more random - you can change this, but it will
                        // kill all sessions (forms that are being filled out).
                        'ref_secret' => 'AAABBBCCCDDDEEEFFF12345678901234567890123456789012345678901234567890',

                        // Session handlers
                        'flash_cache_engine'     => 'redis',
                        'flash_cache_pool_read'  => 'master',
                        'flash_cache_pool_write' => 'master',
                    ],
                ]),

                'locale' => new locale_config([

                    // locale translations active
                    'active' =>  true,

                    // default locale
                    'default' =>  'en',

                    // allowed locales
                    'allowed' =>  [ 'en', 'fr', 'es', 'it' ],

                    // which cache engine should be used to store compiled translation dictionaries
                    'cache_engine' =>  'redis',

                    // which cache pool should translations use to store compiled translation dictionaries
                    'cache_engine_read' =>   'master',
                    'cache_engine_write' =>  'master',
                ]),

                'sql' => new sql_config([
                    'default_pool_read'  => 'master',
                    'default_pool_write' => 'master',

                    'pools' => [
                        'master' => [
                            [
                                'dsn'      => 'mysql:dbname=core;host=127.0.0.1',
                                'user'     => 'root',
                                'password' => '',
                            ],
                        ],
                    ],
                ]),

                'auth' => new auth_config([
                    // authentication cookie name [required]
                    'cookie' => 'cc',

                    // Which site does this user account belong to
                    'site' => 'main',
                ]),

                'memcache' => new memcache_config([
                    //leave black (empty string) if no prefix is needed
                    //this prefix is useful if you have multiple instances of the same code on the same memcache pool (maybe prod/dev on one memcache pool)
                    'key_prefix' => 'sling',

                    'default_pool' => 'master',

                    'pools' => [
                        'master' => [
                            [ 'host' => '127.0.0.1', 'port' => 11211, 'weight' => 1 ],
                        ],
                    ],
                ]),

                'redis' => new redis_config([
                    //leave black (empty string) if no prefix is needed
                    //this prefix is useful if you have multiple instances of the same code base
                    'key_prefix' => 'sling',

                    'default_pool_read'  => 'master',
                    'default_pool_write' => 'master',

                    'pools' => [
                        'master' => [
                            [ 'host' => '127.0.0.1', 'port' => 6379 ],
                        ],
                    ],
                ]),

                'apc' => new apc_config([
                    'key_prefix' => 'sling',
                ]),

                'email' => new email_config([
                    'unsubscribe_secret' => 'AAABBBCCCDDDEEEFFF12345678901234567890123456789012345678901234567890',
                ]),

                'entities' => new cache_config([
                    'default_source_engine'            => 'mysql',
                    'default_source_engine_pool_read'  => 'master',
                    'default_source_engine_pool_write' => 'master',

                    'default_cache_engine'            => 'redis',
                    'default_cache_engine_pool_read'  => 'master',
                    'default_cache_engine_pool_write' => 'master',
                ]),
            ]);
        }
    }