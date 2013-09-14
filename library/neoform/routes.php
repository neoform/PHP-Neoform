<?php

    namespace neoform;

    use neoform\http\route;

    /**
     * Default routes - only index
     */
    class routes implements http\routes {
        public static function get() {
            return new route([
                'controller' => 'controller_index',
            ]);
        }
    }
