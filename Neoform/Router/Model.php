<?php

    namespace Neoform\Router;

    use Neoform\Response;
    use Neoform\Request;
    use Neoform;

    class Model {

        /**
         * @var Request\Model
         */
        protected $request;

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
         * @param Request\Model $request
         * @param ControllerDetails $controllerDetails
         * @param Request\Parameters\Slugs $nonControllerSlugs
         * @param Request\Parameters\Slugs $controllerSlugs
         * @param bool $userAuthenticationRequired
         * @param bool $userForbidden
         * @param bool $redirectRequired
         */
        public function __construct(Request\Model $request,
                                    ControllerDetails $controllerDetails,
                                    Request\Parameters\Slugs $nonControllerSlugs,
                                    Request\Parameters\Slugs $controllerSlugs,
                                    $userAuthenticationRequired,
                                    $userForbidden,
                                    $redirectRequired) {
            $this->request                      = $request;
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
         * @param Response\Builder $responseBuilder
         *
         * @return Response\Http|Response\Response
         * @throws Exception
         */
        public function buildResponse(Response\Builder $responseBuilder) {

//            $beforeRouting = (new Observer\Event\BeforeRouting)
//                ->setRequest($this->request)
//                ->setResponse($responseBuilder)
//                ->attach(new Observer\Listener\SessionTokenChanged)
//                ->notify();

            // @todo Good candidate for an event listener
            if ($this->request->getSession()->hasTokenChanged()) {
                $responseBuilder->setCookie(
                    $this->request->getSession()->sessionCookieKey(),
                    $this->request->getSession()->getToken()
                );
            }

            $this->request->applyRouter($this);

            // Reload/redirect the page since the domain/protocol is wrong
            if ($this->redirectRequired) {
                // @todo I don't really like that we have this in here
                return Response\Http\Builder::redirect($this->getUrl());
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
                throw new Exception('Controller "' . $controllerName . '" is not an instance of Neoform\Router\Controller', 500);
            }

            $controllerActionName = $this->controllerDetails->getActionName();

            // Use the default controller action
            if (! $controllerActionName) {
                $controllerActionName = Controller::DEFAULT_ACTION;
            }

            // Execute the controller action and return the result - do not wrap in try/catch, let the bootstrap handle it
            $view = (new $controllerName($this->request, $responseBuilder))->{$controllerActionName}();

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
         * @return string
         */
        public function getUrl() {
            $server = $this->request->getServer();

            return ($this->controllerDetails->requiresSecure()
                 ? $this->request->getBaseUrl()->getSecureBaseUrl()
                 : $this->request->getBaseUrl()->getRegularBaseUrl())
            . $server->getUri() . ($server->getQuery() ? "?{$server->getQuery()}" : '');
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