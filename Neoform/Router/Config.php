<?php

    namespace Neoform\Router;

    use Neoform;

    class Config extends Neoform\Config\Model {

        /**
         * @return string
         */
        public function getRoutesMapClass() {
            return $this->values['routes_map_class'];
        }
    }