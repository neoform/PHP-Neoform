<?php

    namespace Neoform\Router;

    abstract class Routes {
        /**
         * @return Route\Model
         */
        abstract public function get();
    }