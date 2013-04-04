<?php

    /**
     * This class is used in the route.php config file, which contains all the site's routing information.
     * Each instance of a route is points to a single controller based on a url segment or pattern.
     */
    class http_route {

        /**
         * Relative path to a controller
         *
         * @var string
         */
        protected $controller_path;

        /**
         * Is the URL supposed to be 'secure' (HTTPS if available)
         * @var bool
         */
        protected $secure;

        /**
         * Permission that is required to access this controller
         *
         * @var null|string
         */
        protected $permission;

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
         * Assemble route information
         *
         * @param array $info
         */
        public function __construct(array $info) {
            $this->controller_path = isset($info['controller']) ? (string) $info['controller'] : '';
            $this->secure          = isset($info['secure']) ? (bool) $info['secure'] : false;
            $this->permission      = isset($info['permission']) ? strtolower($info['permission']) : null;
            $this->locale          = isset($info['locale']) && is_array($info['locale']) && count($info['locale']) ? $info['locale'] : null;
            $this->children        = isset($info['children']) && is_array($info['children']) && count($info['children']) ? $info['children'] : null;
        }

        // Don't use these functions in the routes file, they're intended for the core http classes.

        /**
         * Get all routes as a compressed array
         *
         * @param string     $locale
         * @param http_route $route
         * @param string     $route_url
         * @param string     $locale_url
         *
         * @return array
         */
        public function _routes($locale, http_route $route, $route_url='', $locale_url='') {

            $routes = [];

            if ($route->children) {
                foreach ($route->children as $struct => $subroute) {

                    if ($subroute->locale && isset($subroute->locale[$locale])) {
                        $routes[$route_url . '/' . $struct] = $locale_url . '/' . $subroute->locale[$locale];

                    } else {
                        $routes[$route_url . '/' . $struct] = $locale_url . '/' . $struct;
                    }

                    $routes += $subroute->_routes($locale, $subroute, $route_url . '/' . $struct, $routes[$route_url . '/' . $struct]);
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
         */
        public function _controllers($locale) {

            $children = [];
            if ($this->children) {
                foreach ($this->children as $struct => $route) {
                    $children[isset($route->locale[$locale]) ? $route->locale[$locale] : $struct] = $route->_controllers($locale);
                }
            }

            return [
                'secure'          => $this->secure,
                'permission'      => $this->permission,
                'controller_path' => $this->controller_path,
                'children'        => count($children) ? $children: null,
            ];
        }
    }