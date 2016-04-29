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
        public function __construct($routerPath, array $get=[], array $post=[], array $files=[], array $server=[], array $cookies=[]) {

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
         *    When buffer output is turned off, all content and headers generated by views are sent straight to the browser
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
                        $response->setView((new Controller\Error($this->requestBuilder->build(), $response))->action403());
                        return $response->build();

                    case 404: // Not found
                        $response = new Neoform\Response\Http\Builder;
                        $response->setView((new Controller\Error($this->requestBuilder->build(), $response))->action404());
                        return $response->build();

                    case 500: // Server error
                        Neoform\Error\Lib::log($e);
                        $response = new Neoform\Response\Http\Builder;
                        $response->setView((new Controller\Error($this->requestBuilder->build(), $response))->action500());
                        return $response->build();

                    default:
                        $response = new Neoform\Response\Http\Builder;
                        $response->setView((new Controller\Error($this->requestBuilder->build(), $response))->actionGeneric((int) $e->getCode(), $e->getMessage()));
                        return $response->build();
                }

            // Model Exception
            } catch (Neoform\Entity\Exception $e) {
                $response = new Neoform\Response\Http\Builder;
                $response->setView((new Controller\Error($this->requestBuilder->build(), $response))->actionGeneric(404, $e->getMessage(), $e->getDescription()));
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
            } else {
                return Neoform\Response\Http\Builder::redirect("{$request->getBaseUrl()->getSecureBaseUrl()}/account/login");
            }
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