<?php

    namespace Neoform\Request\Parameters\Server;

    class AcceptsEncoding {

        /**
         * @var string[]
         */
        protected $encodings;

        /**
         * @param string[] $encodings
         */
        public function __construct(array $encodings) {
            $this->encodings = $encodings;
        }

        /**
         * @return string[]
         */
        public function toArray() {
            return $this->encodings;
        }

        /**
         * Checks if a specific encoding is accepted
         *
         * @param string $encoding
         *
         * @return bool
         */
        public function accepts($encoding) {
            return in_array($encoding, $this->encodings, true);
        }
    }