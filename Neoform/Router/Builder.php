<?php

    namespace Neoform\Router;

    use Neoform\Request;
    use Neoform;

    class Builder {

        /**
         * @var Neoform\Router\Config
         */
        protected $config;

        /**
         * @var Neoform\Request\Builder
         */
        protected $requestBuilder;

        /**
         * @var ControllerDetails
         */
        protected $controllerDetails;

        /**
         * @var string[]
         */
        protected $controllerSlugs = [];

        /**
         * @var string[]
         */
        protected $nonControllerSlugs;

        /**
         * @var bool
         */
        protected $userAuthenticationRequired = false;

        /**
         * @var bool
         */
        protected $userForbidden = false;

        /**
         * @param Neoform\Request\Builder $requestBuilder
         * @param Neoform\Router\Config   $config
         * @param array                   $controllerMap
         */
        public function __construct(Neoform\Request\Builder $requestBuilder, Neoform\Router\Config $config, array $controllerMap) {
            $this->requestBuilder = $requestBuilder;
            $this->config         = $config;

            $slugs = $this->requestBuilder->getPath()->getSlugs();

            $this->determineController(
                $slugs,
                $controllerMap
            );

            // Assign the remaining slugs as being "non-controller" slugs
            $this->nonControllerSlugs = $this->controllerSlugs ?
                array_slice($slugs->toArray(), count($this->controllerSlugs)) : $slugs->toArray();

            // Name the slug variables based on the controller config
            $this->applyNamedSlugs();
        }

        /**
         * Determine which controller is to be loaded
         *
         * @param Request\Parameters\Slugs $slugs
         * @param array                    $controllerMap
         */
        protected function determineController(Request\Parameters\Slugs $slugs, array $controllerMap) {

            $controllerRequiresSecure = 0;

            $controller = $controllerMap;

            foreach ($slugs->toArray() as $k => $slug) {

                // Doesn't apply to the first (root) slug
                if ($k === 0) {

                } else if (isset($controller[$slug])) {
                    $controller = $controller[$slug];

                // No more controllers matched
                } else {
                    break;
                }

                // If permissions set, make sure user has matching permissions
                if ($controller['resource_ids'] && ! $this->authenticated($controller['resource_ids'])) {
                    break;
                }

                $this->controllerSlugs[] = $slug;

                $controllerRequiresSecure |= (int) $controller['secure'];

                $this->controllerDetails = new ControllerDetails(
                    $controller['controller_class'],
                    $controller['action_name'],
                    (bool) $controllerRequiresSecure,
                    $controller['slugs']
                );

                if (! $controller['children']) {
                    break;
                }

                $controller = $controller['children'];
            }
        }

        /**
         * Apply the naming of slugs based on the selected controller
         */
        protected function applyNamedSlugs() {
            if ($this->controllerDetails->getNamedSlugs()) {
                foreach ($this->controllerDetails->getNamedSlugs() as $k => $slugName) {
                    // Grab the value of the offset and re-assign it in the slug array with the new name
                    if (isset($this->nonControllerSlugs[$k])) {
                        $this->nonControllerSlugs[$slugName] = $this->nonControllerSlugs[$k];

                        // Remove the old key so as to not change the array element count
                        unset($this->nonControllerSlugs[$k]);
                    }
                }
            }
        }

        /**
         * Determines if this controller is authenticated
         *
         * @param array $resourceIds
         *
         * @return bool
         */
        protected function authenticated(array $resourceIds) {

            $auth = $this->requestBuilder->getSession()->getAuth();

            // If user is logged in
            if (! $auth->isLoggedIn()) {
                $this->userAuthenticationRequired = true;
                return false;
            }

            if (! $auth->getUser()->hasAccess($resourceIds)) {
                $this->userForbidden = ! $this->config->isSilentAccessDenied();
                return false;
            }

            return true;
        }

        /**
         * Builds the router model
         *
         * @return Model
         */
        public function build() {
            return new Model(
                $this->requestBuilder,
                $this->controllerDetails,
                new Request\Parameters\Slugs($this->nonControllerSlugs),
                new Request\Parameters\Slugs($this->controllerSlugs),
                $this->userAuthenticationRequired,
                $this->userForbidden,
                $this->isRedirectRequired()
            );
        }

        /**
         * @return bool
         */
        protected function isRedirectRequired() {
            $baseUrl = $this->requestBuilder->getBaseUrl();

            if (! $baseUrl->isValid()) {
                return false;
            }

            if ($this->controllerDetails->requiresSecure()) {
                if (! $baseUrl->isValidSecure()) {
                    return true;
                }
            } else {
                if (! $baseUrl->isValidRegular()) {
                    return true;
                }
            }

            return false;
        }
    }
