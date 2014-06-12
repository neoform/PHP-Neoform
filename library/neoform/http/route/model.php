<?php

    namespace neoform\http\route;

    use neoform;

    /**
     * This class is used in the route.php config file, which contains all the site's routing information.
     * Each instance of a route is points to a single controller based on a url slug or pattern.
     */
    class model {

        /**
         * Class name of the controller
         *
         * @var string
         */
        protected $controller_class;

        /**
         * Action name
         *
         * @var string
         */
        protected $action_name;

        /**
         * Is the URL supposed to be 'secure' (HTTPS if available)
         * @var bool
         */
        protected $secure;

        /**
         * Resource that is required to access this controller
         *
         * @var null|string|array
         */
        protected $resource;

        /**
         * Locale information for the route
         *
         * @var array|null
         */
        protected $locale;

        /**
         * Child routes
         *
         * @var array|null
         */
        protected $children;

        /**
         * URL slugs
         *
         * @var array|null
         */
        protected $slugs;

        /**
         * Assemble route information
         *
         * @param array $info
         */
        public function __construct(array $info) {
            $this->controller_class = isset($info['controller']) ? (string) $info['controller'] : '';
            $this->action_name      = isset($info['action']) ? (string) $info['action'] : null;
            $this->secure           = isset($info['secure']) ? (bool) $info['secure'] : false;
            $this->resource         = isset($info['resources']) ? $info['resources'] : null;
            $this->locale           = isset($info['locale']) && is_array($info['locale']) && $info['locale'] ? $info['locale'] : null;
            $this->children         = isset($info['children']) && is_array($info['children']) && $info['children'] ? $info['children'] : null;
            $this->slugs            = isset($info['slugs']) && is_array($info['slugs']) && $info['slugs'] ? $info['slugs'] : null;
        }

        // Don't use these functions in the routes file, they're intended for the core http classes.

        /**
         * Get all routes as a compressed array
         *
         * @param string $locale
         * @param model  $route
         * @param string $route_url
         * @param string $locale_url
         *
         * @return array
         */
        public function _routes($locale, model $route, $route_url='', $locale_url='') {

            $routes = [];

            if ($route->children) {
                foreach ($route->children as $struct => $subroute) {

                    if ($subroute->locale && isset($subroute->locale[$locale])) {
                        $routes["{$route_url}/{$struct}"] = "{$locale_url}/{$subroute->locale[$locale]}";

                    } else {
                        $routes["{$route_url}/{$struct}"] = "{$locale_url}/{$struct}";
                    }

                    $routes += $subroute->_routes($locale, $subroute, "{$route_url}/{$struct}", $routes["{$route_url}/{$struct}"]);
                }
            }

            return $routes;
        }

        /**
         * Get all controllers as a compressed array
         *
         * @param string $locale
         *
         * @return array
         * @throws neoform\acl\resource\exception
         */
        public function _controllers($locale) {

            // Child controllers
            $children = [];
            if ($this->children) {
                foreach ($this->children as $struct => $route) {
                    $children[isset($route->locale[$locale]) ? $route->locale[$locale] : $struct] = $route->_controllers($locale);
                }
            }

            // This controller
            if ($this->resource) {
                $resource_ids = $this->_get_resource_ids(
                    is_array($this->resource) ? $this->resource : [ $this->resource ]
                );
            } else {
                $resource_ids = [];
            }

            return [
                'secure'           => $this->secure,
                'resource_ids'     => array_values($resource_ids),
                'controller_class' => $this->controller_class,
                'action_name'      => $this->action_name,
                'children'         => $children ? $children : null,
                'slugs'            => $this->slugs,
            ];
        }

        /**
         * Parse the acl resource name(s) and return the acl resource ids
         *
         * @param array $resources
         *
         * @return array
         * @throws neoform\acl\resource\exception
         */
        protected function _get_resource_ids(array $resources) {
            $resource_ids = [];

            foreach ($resources as $resource) {

                if ($resource_names = preg_split('`\s*/\s*`', $resource, -1, PREG_SPLIT_NO_EMPTY)) {

                    // If resource is not nested (eg, "admin")
                    if (count($resource_names) === 1) {
                        if ($resource_id = neoform\entity::dao('acl\resource')->by_parent_name(null, reset($resource_names))) {
                            if ($parent_id = (int) reset($resource_id)) {
                                $resource_ids[] = (int) reset($resource_id);
                            }
                        } else {
                            throw new neoform\acl\resource\exception("Resource \"" . reset($resource_names) . "\" does not exist");
                        }

                    // If resource is nested (eg, "admin:acl:role")
                    } else {
                        $parent_id = null;
                        foreach ($resource_names as $resource_name) {
                            if ($resource_model = neoform\entity::dao('acl\resource')->by_parent_name($parent_id, $resource_name)) {
                                $parent_id = (int) reset($resource_model);
                            } else {
                                throw new neoform\acl\resource\exception("Resource \"{$resource}\" does not exist");
                            }
                        }

                        if ($parent_id) {
                            $resource_ids[] = $parent_id;
                        }
                    }
                }
            }

            return array_unique($resource_ids);
        }
    }