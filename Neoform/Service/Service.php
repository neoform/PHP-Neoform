<?php

    namespace Neoform\Service;

    /**
     * Interface Service
     *
     * Used to pass/inject services into classes for later use. This allows a connection to be lazy-loaded and not
     * opened until necessary.
     *
     * @package Neoform\Service
     */
    interface Service {

        /**
         * Get active service
         *
         * return mixed the actual connection or object
         */
        public function get();

        /**
         * Open/Activate service
         *
         * return $this
         */
        public function open();

        /**
         * Close/Deactivate service
         *
         * return $this
         */
        public function close();
    }