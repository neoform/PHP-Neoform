<?php

    namespace Neoform\Request\Parameters;

    use Neoform\Request;

    class Payload {

        /**
         * @var string|null
         */
        protected $payload;

        /**
         * @var int
         */
        protected $size;

        /**
         * @param string|null $payload
         */
        public function __construct($payload) {
            $this->payload = $payload;
            $this->size    = strlen($payload);
        }

        /**
         * @return string|null
         */
        public function get() {
            return $this->payload;
        }

        /**
         * @return int
         */
        public function getSize() {
            return $this->size;
        }
    }