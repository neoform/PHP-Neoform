<?php

    namespace Neoform\Redirect;

    class Exception extends \Exception {

        protected $url;
        protected $message;

        public function __construct($url=null, $message=null) {
            $this->url     = $url;
            $this->message = $message;
        }

        public function message() {
            return $this->message;
        }

        public function url() {
            return $this->url;
        }
    }