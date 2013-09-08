<?php

    namespace neoform;

    /**
     * Default routes - only index
     */
    class routes implements http_routes {
        public static function get() {
            return new http_route([
                'controller' => 'controller_index',
            ]);
        }
    }
