<?php

    namespace Neoform\Type;

    class decimal {

        protected $intval;
        protected $precision;
        protected $multiplier;

        /**
         * @param integer|float $decimal
         * @param integer       $precision
         */
        public function __construct($decimal, $precision) {
            $this->precision  = (int) $precision;
            $this->multiplier = pow(10, (int) $precision);
            $this->intval     = (int) round(round((float) $decimal, (int) $precision, PHP_ROUND_HALF_UP) * (int) $this->multiplier, 0, PHP_ROUND_HALF_UP);
        }

        /**
         * @param $precision
         *
         * @return $this
         */
        public function convert($precision) {
            if ($this->precision !== (int) $precision) {
                if ($precision > $this->precision) {
                    $this->intval = (int) round($this->intval * pow(10, $precision - $this->precision), 0, PHP_ROUND_HALF_UP);
                } else {
                    $this->intval = (int) round($this->intval / pow(10, $this->precision - $precision), 0, PHP_ROUND_HALF_UP);
                }

                $multiplier      = pow(10, (int) $precision);
                $this->precision = (int) $precision;
            }
            return $this;
        }

        /**
         * Arithmatic Operations
         *
         * @param decimal $num
         *
         * @return $this
         */
        public function add(decimal $num) {
            if ($num->precision() === $this->precision()) {
                $this->intval += $num->_intval();
            } else {
                if ($num->precision() > $this->precision()) {
                    $smaller = $this;
                    $bigger = $num;
                } else {
                    $smaller = $num;
                    $bigger = $this;
                }

                $p = $smaller->precision();              // Store smaller one's precsion
                $smaller->convert($bigger->precision()); // Convert smaller to larger precision
                $this->intval += $num->_intval();        // Add
                $smaller->convert($p);                   // Convert smaller one back to previous precision
            }
            return $this;
        }

        /**
         * @param decimal $num
         *
         * @return $this
         */
        public function subtract(decimal $num) {
            if ($num->precision() === $this->precision()) {
                $this->intval -= $num->_intval();
            } else {
                if ($num->precision() > $this->precision()) {
                    $smaller = $this;
                    $bigger  = $num;
                } else {
                    $smaller = $num;
                    $bigger  = $this;
                }

                $p = $smaller->precision();              // Store smaller one's precsion
                $smaller->convert($bigger->precision()); // Convert smaller to larger precision
                $this->intval -= $num->_intval();        // Subtract
                $smaller->convert($p);                   // Convert smaller one back to previous precision
            }
            return $this;
        }

        /**
         * @param decimal $num
         *
         * @return $this
         */
        public function multiply(decimal $num) {
            if ($num->precision() === $this->precision()) {
                $this->intval = (int) round(($this->intval * $num->_intval()) / pow(10, $this->precision()));
            } else {
                if ($num->precision() > $this->precision()) {
                    $smaller = $this;
                    $bigger  = $num;
                } else {
                    $smaller = $num;
                    $bigger  = $this;
                }

                $p = $smaller->precision();                 // Store smaller one's precsion
                $smaller->convert($bigger->precision());    // Convert smaller to larger precision
                $this->intval = (int) round(($this->intval * $num->_intval()) / pow(10, $this->precision()));
                $smaller->convert($p);                      // Convert smaller one back to previous precision
            }
            return $this;
        }

        /**
         * @param decimal $num
         *
         * @return $this
         */
        public function divide(decimal $num) {
            if ($num->precision() === $this->precision()) {
                $this->intval = (int) round(($this->intval / $num->_intval()) * pow(10, $this->precision()));
            } else {
                if ($num->precision() > $this->precision()) {
                    $smaller = $this;
                    $bigger  = $num;
                } else {
                    $smaller = $num;
                    $bigger  = $this;
                }

                $p = $smaller->precision();                 // Store smaller one's precsion
                $smaller->convert($bigger->precision());    // Convert smaller to larger precision
                $this->intval = (int) round(($this->intval / $num->_intval()) * pow(10, $this->precision()));
                $smaller->convert($p);                      // Convert smaller one back to previous precision
            }
            return $this;
        }

        /*
         * Comparisons
         */

        /**
         * @param decimal $num
         *
         * @return bool
         */
        public function greater_than(decimal $num) {
            $num = clone $num;
            return (bool) ($this->intval > $num->convert($this->precision())->_intval());
        }

        /**
         * @param decimal $num
         *
         * @return bool
         */
        public function greater_than_equals(decimal $num) {
            $num = clone $num;
            return (bool) ($this->intval >= $num->convert($this->precision())->_intval());
        }

        /**
         * @param decimal $num
         *
         * @return bool
         */
        public function less_than(decimal $num) {
            $num = clone $num;
            return (bool) ($this->intval < $num->convert($this->precision())->_intval());
        }

        /**
         * @param decimal $num
         *
         * @return bool
         */
        public function less_than_equals(decimal $num) {
            $num = clone $num;
            return (bool) ($this->intval <= $num->convert($this->precision())->_intval());
        }

        /**
         * @param decimal $num
         *
         * @return bool
         */
        public function equals(decimal $num) {
            $num = clone $num;
            return (bool) ($this->intval === $num->convert($this->precision())->_intval());
        }

        /**
         * @return int
         */
        public function _intval() {
            return $this->intval;
        }

        /**
         * @return int
         */
        public function precision() {
            return $this->precision;
        }

        /**
         * @return number
         */
        public function multiplier() {
            return $this->multiplier;
        }

        /**
         * @return float
         */
        public function value() {
            return (float) round($this->intval / $this->multiplier, $this->precision, PHP_ROUND_HALF_UP);
        }

        /**
         * @return float
         */
        public function val() {
            return (float) round($this->intval / $this->multiplier, $this->precision, PHP_ROUND_HALF_UP);
        }

        /**
         * @return float
         */
        public function floatval() {
            return (float) round($this->intval / $this->multiplier, $this->precision, PHP_ROUND_HALF_UP);
        }

        /**
         * @return string
         */
        public function __tostring() {
            return (string) number_format(round($this->intval / $this->multiplier, $this->precision, PHP_ROUND_HALF_UP), $this->precision);
        }
    }


