PHP Neoform
===========

This is a toolbox I've developed over the years that has been used for
many websites I've built. The primary mission I had when creating this
library was to make the code fast and east to use. The main features
of this toolbox:

- **MVC and Bootstrapping**:
    - `Request\Request` (immutable object composed of the HTTP request 
    data) 
    - Routed via a `Router\Routes` map (cached for performance)
    - `Neoform\Router\Controller` then generates a `Render\View` object 
    (eg, HTML, JSON, or Raw)
    - `Response\Response` object is passed the rendered View 
    - Bootstrap decides what to do with the Response, usually echoing it
- **Service provider** tools via `Service\Service`, allowing for resource 
based objects to only be activated when needed. This helps greatly when 
doing dependency injection whilst avoiding unnecessary resource 
allocation (eg, opening a MySQL or Redis connection).
- **Session** handling via `Session\Auth` that connects with a powerful ACL 
(`Acl`) that allows for user control via roles and groups.
- **Entity** mapping from a data source automatically gets cached at the 
record level. Multiple different types of cache can be employed. Unlike
most entity caching systems, this one can use Memcache/Redis (or others)
and will **never** serve stale cache. 
    - Loading a `Entity\Model` is as easy as: 
    `Neoform\User\Model::fromPk(591);`. 
    If the record does not exist in cache, it will be fetched and saved 
    to cache for every subsequent lookup. 
    - Loading an `Entity\Collection` has the same feature set:
    `Neoform\User\Collection::byStatus(2);`. The result will be cached,
    and the cache will be deleted as soon as any Model in the Collection
    has changed.
- **Configs** that are object based located in an `Config\Environment` file,
which is subsequently cached for performance. Instead of just dumping a 
bunch of random array values into a big messy config file, configs are 
validated twice before being compiled/cached. This means you can always
trust the values found in the config file, even if it's been inherited
multiple times. 
    - Sad Face: `echo isset($config['core']['site_name']) ? $config['core']['site_name'] : 'Oops';` 
    - Happy Face: `echo Neoform\Core\Config::get()->getSiteName();`.
- **Input Validation** is done via an extremely flexible Model/Collection
design pattern that allows for `Input\Validation` objects to be created
for various scenarios, and applied to the `Input\Collection`. If any value
has an error, or is absent whilst listed as required, an 
`Input\Error\Collection` will be made available.
- **Auto-Generation** of `Entity` classes based on MySQL/PostgreSQL 
table mappings. Tables, Fields, Indexes and Foreign keys are used to map 
out the relationships between `Entity` objects. Subdivided into two main
groups (`Entity\Records` and `Entity\Links`) you can interact with the
Entities in a very natural way, and the code is all generated for you
with a simple CLI script.

Composer Install
----------------
If you wish you can install PHP Neoform via [Composer](http://getcomposer.org).

Add ``neoform/neoform`` as a dependency in your project's ``composer.json`` file:

```json
{
    "require": {
        "neoform/neoform": "dev-master"
    }
}
```

Examples
--------

### Sample Index:

```PHP
<?php
    // Include the framework's core
    require_once(realpath(__DIR__ . "/../library/Neoform/Core.php"));

    $core = Neoform\Core::build(
        __DIR__ . '/..',
        'MyApp\Environment\\ProductionEnvironment'
    );

    // Create bootstrap for the MVC
    $bootstrap = new MyApp\Bootstrap(
        (isset($_SERVER['REQUEST_URI']) ? rawurldecode($_SERVER['REQUEST_URI']) : '/'),
        $_GET,
        $_POST,
        $_FILES,
        $_SERVER,
        $_COOKIE
    );

    $bootstrap
        ->buildResponse()
        ->render();
```


### Sample Bootstrap:

```PHP
<?php
    namespace MyApp;
    use Neoform;

    /*
     * Make a website - standard/simple bootstrap
     */
    class Bootstrap {

        /**
         * @var Neoform\Request\Builder
         */
        private $requestBuilder;

        /**
         * Create bootstrap application
         *
         * @param string $routerPath
         * @param array  $get
         * @param array  $post
         * @param array  $files
         * @param array  $server
         * @param array  $cookies
         */
        public function __construct($routerPath, array $get=[], array $post=[], array $files=[], 
                                    array $server=[], array $cookies=[]) {

            $this->requestBuilder = (new Neoform\Request\Builder(Neoform\Router\Config::get()))
                ->setPath(
                    $routerPath,
                    Neoform\Locale\Config::get()
                )
                ->setGet($get)
                ->setPost($post)
                ->setFiles($files)
                ->setServer($server)
                ->setCookies($cookies)
                ->loadSession();
        }

        /**
         * Executes the bootstrap
         * When buffer output is turned off, all content and headers generated by views are sent straight to the browser
         *
         * @return Neoform\Response\Response
         */
        public function buildResponse() {
            try {
                // Get locale from URL
                $locale = Neoform\Locale::getService()->get();
                if ($localeIso2 = $this->requestBuilder->getPath()->getSlugs()->getLocaleIso2()) {
                    $locale->set($localeIso2);
                }

                // Get routes
                $dao = new Neoform\Router\Route\Dao(
                    Neoform\Core::get()->getCachePath(),
                    Neoform\Router\Config::get()
                );

                // Get routes based on locale
                $routeInfo = $dao->get($locale->get());
                $locale->set_routes($routeInfo['routes']);

                // Router Builder
                $routerBuilder = new Neoform\Router\Builder(
                    $this->requestBuilder,
                    Neoform\Router\Config::get(),
                    $routeInfo['controllers']
                );

                // Build the router
                $router = $routerBuilder->build();

                // Apply controller/non-controller slugs to request builder
                $this->requestBuilder->applyRouter($router);

                // Build the response based on the router's signals
                return $router->buildResponse(
                    $this->requestBuilder->build(),
                    new Neoform\Response\Http\Builder
                );

            } catch (Neoform\Router\Exception $e) {
                switch ((int) $e->getCode()) {
                    case 401: // Login required
                        // Bounce back to current URL after login
                        return $this->requireLogin($this->requestBuilder->build(), $e->getMessage());

                    case 403: // Access denied
                        $response = new Neoform\Response\Http\Builder;
                        $response->setView(
                            (new Controller\Error($this->requestBuilder->build(), $response))->action403()
                            );
                        return $response->build();

                    case 404: // Not found
                        $response = new Neoform\Response\Http\Builder;
                        $response->setView(
                            (new Controller\Error($this->requestBuilder->build(), $response))->action404()
                        );
                        return $response->build();

                    case 500: // Server error
                        Neoform\Error\Lib::log($e);
                        $response = new Neoform\Response\Http\Builder;
                        $response->setView(
                            (new Controller\Error($this->requestBuilder->build(), $response))->action500()
                        );
                        return $response->build();

                    default:
                        $response = new Neoform\Response\Http\Builder;
                        $response->setView(
                            (new Controller\Error($this->requestBuilder->build(), $response))
                                ->actionGeneric((int) $e->getCode(), $e->getMessage())
                        );
                        return $response->build();
                }

            // Model Exception
            } catch (Neoform\Entity\Exception $e) {
                $response = new Neoform\Response\Http\Builder;
                $response->setView(
                    (new Controller\Error($this->requestBuilder->build(), $response))
                        ->actionGeneric(404, $e->getMessage(), $e->getDescription())
                );
                return $response->build();

            // All other exceptions
            } catch (\Exception $e) {
                Neoform\Error\Lib::log($e);
                $response = new Neoform\Response\Http\Builder;
                $response->setView((new Controller\Error($this->requestBuilder->build(), $response))->action500());
                return $response->build();
            }
        }

        /**
         * @param Neoform\Request\Model $request
         * @param string|null           $message
         *
         * @return Neoform\Response\Response
         */
        private function requireLogin(Neoform\Request\Model $request, $message=null) {
            $request->getSession()->getFlash()->set('login_bounce', $request->getServer()->getUri());

            if ($message) {
                $request->getSession()->getFlash()->set('login_message', $message);
            }

            if ($request->getServer()->getAccepts()->accepts('application/json')) {
                return $this->jsonStatusResponse('login');
            }
            
            return Neoform\Response\Http\Builder::redirect(
                "{$request->getBaseUrl()->getSecureBaseUrl()}/account/login"
            );
        }

        /**
         * @param string $status
         *
         * @return Neoform\Response\Http
         */
        private function jsonStatusResponse($status) {
            $response = new Neoform\Response\Http\Builder;
            $json = new Neoform\Render\Json;
            $json->set('status', $status);
            $response->setView($json, 'application/json');
            return $response->build();
        }
    }
```

### Sample Controller:

```PHP
<?php
    namespace MyApp\Controller;
    use Neoform;
    use MyApp;

    class Info extends MyApp\Controller {

        public function defaultAction() {
            $view = new Neoform\Render\Html;
            $this->applyDefaults($view);

            if ($this->request->getGet()->count() || $this->request->getParameters()->count()) {
                return $this->show404();
            }

            $view->slug = $slug = (string) $this->request->getNonControllerSlugs()->get('slug');

            $validPages = [
                'about'        => 'About Us',
                'merchants'    => 'Merchants',
                'advertising'  => 'Advertising',
                'partnerships' => 'Partnerships',
                'syndication'  => 'Syndication',
                'marketing'    => 'Marketing',
                'privacy'      => 'Privacy Policy',
                'tos'          => 'Terms of Service',
                'contact'      => 'Contact',
            ];

            if (! isset($validPages[$slug])) {
                return $this->show404();
            }

            $view->metaTitle = "{$validPages[$slug]} - Example.ca";

            $view->applyTemplate('info');
            $this->response->setView($view);
        }
    }
```

### Sample Router Mapping

```PHP
<?php
    namespace MyApp;
    use Neoform\Router\Route\Model as Route;
    use Neoform;

    class Routes extends Neoform\Router\Routes {
        /**
         * @return Route
         */
        public function get() {
            return new Route([
                'controller' => 'MyApp\Controller\Index',
                'children'   => [
                    // API
                    'api' => new Route([
                        'controller' => 'MyApp\Controller\Api',
                        'secure' => true,
                        'slugs' => [
                            0 => 'action',
                        ],
                    ]),
                    // Account
                    'account' => new Route([
                        'controller' => 'MyApp\Controller\Account',
                        'secure' => true,
                        'locale' => [
                            'en' => 'account',
                            'fr' => 'compte',
                        ],
                        'children' => [
                            'login' => new Route([
                                'controller' => 'MyApp\Controller\Account\Login',
                                'locale' => [
                                    'en' => 'login',
                                    'fr' => 'connexion',
                                ],
                            ]),
                        ],
                    ]),
                ],
            ]);
        }
    }
                        
```


### Sample Environment (Configs):

```PHP
<?php
    namespace MyApp\Environment;
    use MyApp;
    use Neoform;

    class Production extends Neoform\Config\Environment {
        public function getName() {
            return 'Production';
        }

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

            // Cookies (default values)
            $this->merge(new Neoform\Request\Parameters\Cookies\Config\Builder);

            // Sessions
            $this->merge(new Neoform\Session\Config\Builder([
                // random string to make the ref code more random - you can change this, but it will
                // kill all sessions (forms that are being filled out).
                'xsrf_salt' => 'abcdefghijklmnopqrstuvwxyz1234567890.,/!@#$%^&*()*',

                // Session handlers
                'flash_cache_engine' => 'Neoform\Redis',

                // Which server is used when reading
                'flash_cache_pool_read' => 'master',

                // Which server is used when writing
                'flash_cache_pool_write' => 'master',
            ]));
        }
    }

```

Questions/Comments
------------------

Since this is a pet project of mine and the toolbox is relatively simple, 
if you need any help with it, feel free to contact me at: ian@oshaughnessy.cc