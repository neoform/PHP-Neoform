<?php

    namespace Neoform\Entity;

    //crush all the built in exception crap since we don't need to know anything relating to stack trace for these model exceptions...

    class Exception extends \Exception {

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
         * @return null|string
         */
        public function getDescription() {
            return $this->description;
        }
    }