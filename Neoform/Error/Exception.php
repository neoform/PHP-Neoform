<?php

    namespace Neoform\Error;

    class Exception extends \Exception {

        /**
         * @var string|null
         */
        protected $message;

        /**
         * @var string|null
         */
        protected $description;

        /**
         * @param string|null $message
         * @param string|null $description
         */
        public function __construct($message=null, $description=null) {
            $this->message     = $message;
            $this->description = $description;
        }

        /**
         * @return string|null
         */
        public function message() {
            return $this->message;
        }

        /**
         * @return string|null
         */
        public function description() {
            return $this->description;
        }
    }