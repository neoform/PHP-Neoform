<?php

    namespace Neoform\Router;

    use Neoform\Response;
    use Neoform\Request;
    use Neoform;

    class Model {

        /**
         * @var ControllerDetails
         */
        protected $controllerDetails;

        /**
         * @var Request\Parameters\Slugs
         */
        protected $nonControllerSlugs;

        /**
         * @var Request\Parameters\Slugs
         */
        protected $controllerSlugs;

        /**
         * @var bool
         */
        protected $userAuthenticationRequired;

        /**
         * @var bool
         */
        protected $userForbidden;

        /**
         * @var bool
         */
        protected $redirectRequired;

        /**
         * @param Request\Builder          $requestBuilder
         * @param ControllerDetails        $controllerDetails
         * @param Request\Parameters\Slugs $nonControllerSlugs
         * @param Request\Parameters\Slugs $controllerSlugs
         * @param bool                     $userAuthenticationRequired
         * @param bool                     $userForbidden
         * @param bool                     $redirectRequired
         */
        public function __construct(Request\Builder $requestBuilder,
                                    ControllerDetails $controllerDetails,
                                    Request\Parameters\Slugs $nonControllerSlugs,
                                    Request\Parameters\Slugs $controllerSlugs,
                                    $userAuthenticationRequired,
                                    $userForbidden,
                                    $redirectRequired) {
            $this->controllerDetails            = $controllerDetails;
            $this->nonControllerSlugs           = $nonControllerSlugs;
            $this->controllerSlugs              = $controllerSlugs;
            $this->userAuthenticationRequired   = $userAuthenticationRequired;
            $this->userForbidden                = $userForbidden;
            $this->redirectRequired             = $redirectRequired;
        }

        /**
         * Execute the controller/action
         *
         * @param Request\Model    $request
         * @param Response\Builder $responseBuilder
         *
         * @return Response\Http|Response\Response
         * @throws Exception
         */
        public function buildResponse(Request\Model $request, Response\Builder $responseBuilder) {

            // Reload/redirect the page since the domain/protocol is wrong
            if ($this->redirectRequired) {
                return $responseBuilder
                    ->reset()
                    ->setResponseCode(303)
                    ->setHeader('Location', (string) $this->getUrl($request))
                    ->build();
            }

            // Show login page
            if ($this->userAuthenticationRequired) {
                throw new Exception('Unauthorized', 401);
            }

            // Show access denied
            if ($this->userForbidden) {
                throw new Exception('Forbidden', 403);
            }

            $controllerName = $this->controllerDetails->getClassName();

            // Controller doesn't exist, 404 not found
            if (! $controllerName) {
                throw new Exception('Controller name not set', 404);
            }

            if (! is_subclass_of($controllerName, 'Neoform\Router\Controller')) {
                throw new Exception("Controller \"{$controllerName}\" is not an instance of Neoform\\Router\\Controller", 500);
            }

            $controllerActionName = $this->controllerDetails->getActionName();

            // Use the default controller action
            if (! $controllerActionName) {
                $controllerActionName = Controller::DEFAULT_ACTION;
            }

            // Execute the controller action and return the result - do not wrap in try/catch, let the bootstrap handle it
            $view = (new $controllerName($request, $responseBuilder))->{$controllerActionName}();

            // The controller action can return a view or null. If null assume there's no view, or it was already added
            // top the response by the view
            if ($view) {
                if (! ($view instanceof Neoform\Render\View)) {
                    throw new Exception('Controller\'s action must only return a Render\View object', 500);
                }

                $responseBuilder->setView($view);
            }

            // Build the response
            $response = $responseBuilder->build();

            // Make sure the response is a response
            if (! ($response instanceof Response\Response)) {
                throw new Exception('Controller\'s response was not a valid Response object', 500);
            }

            return $response;
        }

        /**
         * Gets the correct URL based on the protocol required by the controller
         *
         * @param Request\Model $request
         *
         * @return string
         */
        protected function getUrl(Request\Model $request) {
            return ($this->controllerDetails->requiresSecure()
                 ? $request->getBaseUrl()->getSecureBaseUrl()
                 : $request->getBaseUrl()->getRegularBaseUrl())
                        . $request->getServer()->getUri()
                        . ($request->getServer()->getQuery() ? "?{$request->getServer()->getQuery()}" : '');
        }

        /**
         * @return Request\Parameters\Slugs
         */
        public function getNonControllerSlugs() {
            return $this->nonControllerSlugs;
        }

        /**
         * @return Request\Parameters\Slugs
         */
        public function getControllerSlugs() {
            return $this->controllerSlugs;
        }
    }