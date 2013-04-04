<?php

    class type_currency {

        protected $int;
        protected $float;

        public function __construct($val) {
            $this->int         = (int) $val;
            $this->float    = (float) $val;
        }

        public function val() {
            return $this->float;
        }

        public function formatted($abs=false) {
            if ($abs) {
                $float     = abs($this->float);
                $int     = abs($this->int);

                //check if the rounded version is the same
                if (round($float, 2) == $int) {
                    return (string) $int;
                } else {
                    return (string) number_format($float, 2);
                }
            } else {
                //check if the rounded version is the same
                if (round($this->float, 2) == $this->int) {
                    return (string) number_format($this->int);
                } else {
                    return (string) number_format($this->float, 2);
                }
            }
        }

        public function __tostring() {
            return $this->formatted();
        }
    }