<?php

    namespace Neoform\Router;

    class ControllerDetails {

        /**
         * @var string
         */
        protected $className;

        /**
         * @var string
         */
        protected $actionName;

        /**
         * @var bool
         */
        protected $requiresSecure;

        /**
         * @var array
         */
        protected $namedSlugs;

        /**
         * @param string $className
         * @param string $actionName
         * @param bool   $requiresSecure
         * @param array  $namedSlugs
         */
        public function __construct($className, $actionName, $requiresSecure, array $namedSlugs=null) {
            $this->className      = $className ? "\\{$className}" : null;
            $this->actionName     = $actionName;
            $this->requiresSecure = $requiresSecure;
            $this->namedSlugs     = $namedSlugs ?: [];
        }

        /**
         * @return string
         */
        public function getClassName() {
            return $this->className;
        }

        /**
         * @return string
         */
        public function getActionName() {
            return $this->actionName;
        }

        /**
         * @return boolean
         */
        public function requiresSecure() {
            return $this->requiresSecure;
        }

        /**
         * @return array
         */
        public function getNamedSlugs() {
            return $this->namedSlugs;
        }
    }