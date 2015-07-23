<?php

    namespace Neoform\Router;

    use Neoform\Response;

    abstract class Controller {

        const DEFAULT_ACTION = 'defaultAction';

        /**
         * Default controller action
         */
        abstract public function defaultAction();
    }