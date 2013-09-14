<?php

    namespace neoform\entity;

    //crush all the built in exception crap since we don't need to know anything relating to stack trace for these model exceptions...

    class exception extends \exception {

        protected $message;
        protected $description;

        public function __construct($message=null, $description=null) {
            $this->message = $message;
            $this->description = $description;
        }

        public function message($message=null) {
            if ($message !== null) {
                $this->message = $message;
            } else {
                return $this->message;
            }
        }

        public function description($description=null) {
            if ($description !== null) {
                $this->description = $description;
            } else {
                return $this->description;
            }
        }
    }