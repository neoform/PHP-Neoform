<?php

    namespace neoform;

    use neoform\http\route\model as route;

    /**
     * Default routes - only index
     */
    class routes extends http\routes {
        public function get() {
            return new route([
                'controller' => 'controller_index',
            ]);
        }
    }
