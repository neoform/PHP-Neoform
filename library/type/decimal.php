<?php

    class type_decimal {

        protected $intval;
        protected $precision;
        protected $multiplier;

        public function __construct($decimal, $precision) {
            $this->precision     = (int) $precision;
            $this->multiplier     = pow(10, (int) $precision);
            $this->intval         = (int) round(round((float) $decimal, (int) $precision, PHP_ROUND_HALF_UP) * (int) $this->multiplier, 0, PHP_ROUND_HALF_UP);
        }

        public function convert($precision) {
            if ($this->precision !== (int) $precision) {
                if ($precision > $this->precision) {
                    $this->intval = (int) round($this->intval * pow(10, $precision - $this->precision), 0, PHP_ROUND_HALF_UP);
                } else {
                    $this->intval = (int) round($this->intval / pow(10, $this->precision - $precision), 0, PHP_ROUND_HALF_UP);
                }

                $multiplier         = pow(10, (int) $precision);
                $this->precision     = (int) $precision;
            }
            return $this;
        }

        // Arithmatic Operations
        public function add(type_decimal $num) {
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

                $p = $smaller->precision();                    // Store smaller one's precsion
                $smaller->convert($bigger->precision());    // Convert smaller to larger precision
                $this->intval += $num->_intval();            // Add
                $smaller->convert($p);                         // Convert smaller one back to previous precision
            }
            return $this;
        }

        public function subtract(type_decimal $num) {
            if ($num->precision() === $this->precision()) {
                $this->intval -= $num->_intval();
            } else {
                if ($num->precision() > $this->precision()) {
                    $smaller     = $this;
                    $bigger     = $num;
                } else {
                    $smaller     = $num;
                    $bigger     = $this;
                }

                $p = $smaller->precision();                    // Store smaller one's precsion
                $smaller->convert($bigger->precision());    // Convert smaller to larger precision
                $this->intval -= $num->_intval();            // Subtract
                $smaller->convert($p);                         // Convert smaller one back to previous precision
            }
            return $this;
        }

        public function multiply(type_decimal $num) {
            if ($num->precision() === $this->precision()) {
                $this->intval = (int) round(($this->intval * $num->_intval()) / pow(10, $this->precision()));
            } else {
                if ($num->precision() > $this->precision()) {
                    $smaller     = $this;
                    $bigger     = $num;
                } else {
                    $smaller     = $num;
                    $bigger     = $this;
                }

                $p = $smaller->precision();                    // Store smaller one's precsion
                $smaller->convert($bigger->precision());    // Convert smaller to larger precision
                $this->intval = (int) round(($this->intval * $num->_intval()) / pow(10, $this->precision()));
                $smaller->convert($p);                         // Convert smaller one back to previous precision
            }
            return $this;
        }

        public function divide(type_decimal $num) {
            if ($num->precision() === $this->precision()) {
                $this->intval = (int) round(($this->intval / $num->_intval()) * pow(10, $this->precision()));
            } else {
                if ($num->precision() > $this->precision()) {
                    $smaller     = $this;
                    $bigger     = $num;
                } else {
                    $smaller     = $num;
                    $bigger     = $this;
                }

                $p = $smaller->precision();                    // Store smaller one's precsion
                $smaller->convert($bigger->precision());    // Convert smaller to larger precision
                $this->intval = (int) round(($this->intval / $num->_intval()) * pow(10, $this->precision()));
                $smaller->convert($p);                         // Convert smaller one back to previous precision
            }
            return $this;
        }

        // Comparisons
        public function greater_than(type_decimal $num) {
            $num = clone $num;
            return (bool) ($this->intval > $num->convert($this->precision())->_intval());
        }

        public function greater_than_equals(type_decimal $num) {
            $num = clone $num;
            return (bool) ($this->intval >= $num->convert($this->precision())->_intval());
        }

        public function less_than(type_decimal $num) {
            $num = clone $num;
            return (bool) ($this->intval < $num->convert($this->precision())->_intval());
        }

        public function less_than_equals(type_decimal $num) {
            $num = clone $num;
            return (bool) ($this->intval <= $num->convert($this->precision())->_intval());
        }

        public function equals(type_decimal $num) {
            $num = clone $num;
            return (bool) ($this->intval === $num->convert($this->precision())->_intval());
        }

        // Getters
        public function _intval() {
            return $this->intval;
        }

        public function precision() {
            return $this->precision;
        }

        public function multiplier() {
            return $this->multiplier;
        }

        public function value() {
            return (float) round($this->intval / $this->multiplier, $this->precision, PHP_ROUND_HALF_UP);
        }

        public function val() {
            return (float) round($this->intval / $this->multiplier, $this->precision, PHP_ROUND_HALF_UP);
        }

        public function floatval() {
            return (float) round($this->intval / $this->multiplier, $this->precision, PHP_ROUND_HALF_UP);
        }

        public function __tostring() {
            return (string) number_format(round($this->intval / $this->multiplier, $this->precision, PHP_ROUND_HALF_UP), $this->precision);
        }
    }


