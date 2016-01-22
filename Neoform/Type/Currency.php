<?php

    namespace Neoform\Type;

    class Currency {

        protected $int;
        protected $float;
        protected $precision;

        public function __construct($val, $precision=2) {
            $this->int       = (int) $val;
            $this->float     = (float) $val;
            $this->precision = (int) $precision;
        }

        /**
         * @return float
         */
        public function val() {
            return $this->float;
        }

        /**
         * Return formatted currency
         *
         * @param bool $abs
         *
         * @return string
         */
        public function formatted($abs=false) {
            if ($abs) {
                $float = abs($this->float);
                $int   = abs($this->int);

                //check if the rounded version is the same
                if (round($float, $this->precision) == $int) {
                    return (string) $int;
                } else {
                    return (string) number_format($float, $this->precision);
                }
            } else {
                //check if the rounded version is the same
                if (round($this->float, $this->precision) == $this->int) {
                    return (string) number_format($this->int);
                } else {
                    return (string) number_format($this->float, $this->precision);
                }
            }
        }

        /**
         * @return string
         */
        public function __toString() {
            return $this->formatted();
        }
    }