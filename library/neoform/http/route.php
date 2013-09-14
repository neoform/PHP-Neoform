<?php

    namespace neoform\http;

    use neoform;

    /**
     * This class is used in the route.php config file, which contains all the site's routing information.
     * Each instance of a route is points to a single controller based on a url segment or pattern.
     */
    class route {

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
         * URL segments
         *
         * @var array|null
         */
        protected $segments;

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
            $this->segments         = isset($info['segments']) && is_array($info['segments']) && $info['segments'] ? $info['segments'] : null;
        }

        // Don't use these functions in the routes file, they're intended for the core http classes.

        /**
         * Get all routes as a compressed array
         *
         * @param string $locale
         * @param route  $route
         * @param string $route_url
         * @param string $locale_url
         *
         * @return array
         */
        public function _routes($locale, route $route, $route_url='', $locale_url='') {

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

            $children = [];
            if ($this->children) {
                foreach ($this->children as $struct => $route) {
                    $children[isset($route->locale[$locale]) ? $route->locale[$locale] : $struct] = $route->_controllers($locale);
                }
            }

            $resource_ids = [];
            if ($this->resource) {
                $resources = is_array($this->resource) ? $this->resource : [ $this->resource ];
                foreach (neoform\entity::dao('acl\resource')->by_name_multi($resources) as $resource_id) {
                    if ($resource_id = (int) current($resource_id)) {
                        $resource_ids[$resource_id] = $resource_id;
                    } else {
                        throw new neoform\acl\resource\exception(
                            'Resource' . (count($resources) > 1 ? 's' : '') . ' "' . join(", ", $resources) . '" do' . (count($resources) > 1 ? 'es' : '') . ' not exist'
                        );
                    }
                }
            }

            return [
                'secure'           => $this->secure,
                'resource_ids'     => array_values($resource_ids),
                'controller_class' => $this->controller_class,
                'action_name'      => $this->action_name,
                'children'         => $children ? $children : null,
                'segments'         => $this->segments,
            ];
        }
    }