<?php

    namespace neoform;

    /**
     * Default routes - only index
     */
    class routes implements http\routes {
        public static function get() {
            return new http\route([
                'controller' => 'controller_index',
            ]);
        }
    }
