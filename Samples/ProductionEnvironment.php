<?php

    namespace MyApp\Environment;

    use Neoform;

    class ProductionEnvironment extends Neoform\Config\Environment {

        protected function definitions() {

            // Core
            $this->merge(new Neoform\Core\Config\Builder([
                'site_name'                       => 'Example.com',
                'default_error_controller'        => 'MyApp\Controller\Error',
                'default_error_controller_action' => 'action500',
            ]));

            // Router
            $this->merge(new Neoform\Router\Config\Builder([
                'domain' => 'example.com',

                'https' => [
                    'regular' => true,
                    'secure'  => true,
                ],

                // Required subdomains - default
                'subdomain_default' => [
                    'regular' => 'www',
                    'secure'  => 'www',
                ],

                // CDN base URL
                'cdn' => 'cdn.example.com',

                // Routing map
                'routes_map_class' => 'MyApp\Routes',
            ]));

            // Cookies
            $this->merge(new Neoform\Request\Parameters\Cookies\Config\Builder);

            // Sessions
            $this->merge(new Neoform\Session\Config\Builder([
                // random string to make the ref code more random - you can change this, but it will
                // kill all sessions (forms that are being filled out).
                'xsrf_salt' => 'abcdef123456',

                // Session handlers
                'flash_cache_engine' => 'Neoform\Redis',

                // Which server is used when reading
                'flash_cache_pool_read' => 'master',

                // Which server is used when writing
                'flash_cache_pool_write' => 'master',
            ]));

            // Assets
            $this->merge(new Neoform\Assets\Config\Builder([

                'enabled' => true,

                'types' => [
                    'css' => [
                        'path'      => '/var/www/current/static/public/css',
                        'url'       => '//cdn.example.com/css',
                        'processor' => '\Neoform\Assets\Processor\Css',
                    ],
                    'js' => [
                        'path' => '/var/www/current/static/public/js',
                        'url'  => '//cdn.example.com/js',
                    ],
                ],
            ]));

            // Assets Processor CSS
            $this->merge(new Neoform\Assets\Processor\Css\Config\Builder([
                'search_replace' => [
                    '//dev.example.local/' => '//cdn.example.com/',
                ],
            ]));

            // Locale
            $this->merge(new Neoform\Locale\Config\Builder([

                // locale translations active
                'active' => false,

                // default locale
                'default' => 'en',

                // allowed locales
                'allowed' => [ 'en', 'fr', ],

                // which cache engine should be used to store compiled translation dictionaries
                'cache_engine' => 'Neoform\Redis',

                // which cache pool should translations use to store compiled translation dictionaries
                // Which server is used when reading
                'cache_engine_read' => 'master',

                // Which server is used when writing
                'cache_engine_write' => 'master',
            ]));

            // Web
            $this->merge(new Neoform\Web\Config\Builder([
                'user_agent' => 'Mozilla/5.0 (compatible; PHP Neoform crawlbot/1.0)',
            ]));

            // SQL
            $this->merge(new Neoform\Sql\Config\Builder([

                // Which server is used when reading
                'default_pool_read' => 'master',

                // Which server is used when writing
                'default_pool_write' => 'master',

                // SQL Server connections
                'pools' => [
                    'master' => [
                        [
                            'dsn'      => 'mysql:dbname=neoform;host=localhost',
                            'user'     => 'phpneoform',
                            'password' => 'password',
                        ],
                    ],
                ],
            ]));

            // Auth
            $this->merge(new Neoform\Auth\Config\Builder);

            // Memcache
            $this->merge(new Neoform\Memcache\Config\Builder([
                //leave black (empty string) if no prefix is needed
                //this prefix is useful if you have multiple instances of the same code on the same memcache pool (maybe prod/dev on one memcache pool)
                'key_prefix' => 'neo',

                'default_pool' => 'master',

                // Memcache Server connections
                'pools' => [
                    'master' => [
                        [ 'host' => '127.0.0.1', 'port' => 11211, 'weight' => 1 ],
                    ],
                ],
            ]));

            // Redis
            $this->merge(new Neoform\Redis\Config\Builder([
                //leave black (empty string) if no prefix is needed
                //this prefix is useful if you have multiple instances of the same code base
                'key_prefix' => 'neo',

                // Which server is used when reading
                'default_pool_read' => 'master',

                // Which server is used when writing
                'default_pool_write' => 'master',

                // Redis Server connections
                'pools' => [
                    'master' => [
                        [ 'host' => '127.0.0.1', 'port' => 6379, 'database' => 1, ],
                    ],
                    'auth' => [
                        [ 'host' => '127.0.0.1', 'port' => 6379, 'database' => 2, ],
                    ],
                ],
            ]));

            // Email
            $this->merge(new Neoform\Email\Config\Builder([
                // Hash secret used for generating email unsubscribe links - should be as random as possible
                'unsubscribe_secret' => 'abcdefg123456',
            ]));

            // Entity
            $this->merge(new Neoform\Entity\Config\Builder([

                'defaults' => [
                    'source_engine'            => 'MySQL',
                    'source_engine_pool_read'  => 'master',
                    'source_engine_pool_write' => 'master',

                    'cache_engine'            => 'Redis',
                    'cache_engine_pool_read'  => 'master',
                    'cache_engine_pool_write' => 'master',

                    'cache_meta_engine'       => 'Redis',
                    'cache_meta_engine_pool'  => 'master',

                    'cache_use_binary_keys'   => true,
                ],

                'overrides' => [
                    'Neoform\Auth' => [
                        'source_engine'            => 'Redis',
                        'source_engine_pool_read'  => 'auth',
                        'source_engine_pool_write' => 'auth',
                        'cache_engine'             => null,
                    ],
                ],
            ]));

            // Encrypt
            $this->merge(new Neoform\Encrypt\Config\Builder);
        }
    }
