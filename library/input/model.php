<?php

    class input_model {

        protected $val;
        protected $data     = [];
        protected $error;
        protected $optional = false;
        protected $is_empty;

        /**
         * @param string|int|float|null $val
         */
        public function __construct($val=null) {
            $this->val      = $val;
            $this->is_empty = ! (bool) strlen(trim((string) $this->val));
        }

        /**
         * @return string
         */
        public function __tostring() {
            return (string) $this->val;
        }

        /**
         * @param $k
         *
         * @return mixed
         */
        public function __get($k) {
            return $this->$k();
        }

        /**
         * This is not used, it's a dummy function
         *
         * @param null $v1
         * @param null $v2
         *
         * @return input_model|null
         */
        public function count($v1=null, $v2=null) {
            if ($v1 !== null || $v2 !== null) {
                return $this;
            }
            return null; //i'm not an array
        }

        /**
         * $input->callback = function() { … };
         *
         * @param string   $k
         * @param callable $v
         *
         * @return input_model
         */
        public function __set($k, $v) {
            if ($k === 'callback') {
                if (! $this->error && (! $this->optional || ! $this->is_empty)) {
                    $v($this);
                }
            }
            return $this;
        }

        /**
         * Set value
         *
         * @param $v
         *
         * @return input_model
         */
        public function set($v) {
            $this->val = $v;
            return $this;
        }

        /**
         * Reset local errors
         *
         * @return input_model
         */
        public function reset_errors() {
            $this->error = null;
            return $this;
        }

        /**
         * Get this object
         *
         * @return input_model
         */
        public function get() {
            return $this;
        }

        /**
         * Sets temporary local data
         *
         * @param string $k key
         * @param mixed $v value
         *
         * @return input_model|mixed
         */
        public function data($k=null, $v=null) {
            if ($v !== null) {
                $this->data[$k] = $v;
                return $this;
            } else if (isset($this->data[$k])) {
                return $this->data[$k];
            }
        }

        /**
         * Value of the input
         *
         * @return float|int|null|string
         */
        public function val() {
            return $this->val;
        }

        /**
         * Is this input valid
         *
         * @return bool
         */
        public function is_valid() {
            return ! $this->error;
        }

        /**
         * Return errors if any
         *
         * @param string $set
         *
         * @return mixed|null
         */
        public function errors($set=null) {
            if ($set) {
                $this->error = $set;
            } else {
                return $this->error;
            }
        }

        /**
         * Returns new exception with errors in it
         *
         * @return input_exception
         */
        public function exception() {
            return new input_exception(new input_error_collection([
                $this->errors()
            ]));
        }

        /**
         * Dummy function
         *
         * @return input_model
         */
        public function each() {
            if (! $this->error && (! $this->optional || ! $this->is_empty)) {
                $this->errors('invalid array');
            }
            return $this;
        }

        /**
         * Dummy function
         *
         * @return input_model
         */
        public function unique() {
            if (! $this->error && (! $this->optional || ! $this->is_empty)) {
                $this->errors('invalid array');
            }
            return $this;
        }

        /**
         * Run a callback on this input
         *
         * @param $func
         *
         * @return input_model
         */
        public function callback($func) {
            $func($this);
            return $this;
        }

        //
        // Modifiers
        //

        public function cast($type, $strict=false) {
            if (is_array($type)) {
                $this->errors('invalid type');
            } else {

                // Cast a value. When strict mode is off, strings that are equal to "null", "undefined", "true", "false" are converted to their proper type.
                // This is useful because some browsers are stupid and will pass these values as strings intead of the litterals they are.
                if (! $strict) {
                    $v = strtolower($this->val);
                    if ($v === 'true') {
                        $this->val = true;
                    } else if ($v === 'false') {
                        $this->val = false;
                    } else if ($v === 'null' || $v === 'undefined') {
                        $this->val = null;
                        $this->is_empty = true;
                    }
                }

                switch ($type) {
                    case 'bool':
                    case 'boolean':
                        $this->val = (boolean) $this->val;
                        break;

                    case 'str':
                    case 'string':
                        $this->val = (string) $this->val;
                        break;

                    case 'int':
                    case 'integer':
                        $this->val = (int) $this->val;
                        break;

                    case 'float':
                        $this->val = (float) $this->val;
                        break;

                    case 'number':
                        $this->val = floatval(preg_replace('`[^0-9\.\-]`is', '', $this->val));
                        break;
                }
            }

            return $this;
        }

        //if the value is empty(), nullify it.
        public function nullify() {
            if (empty($this->val)) {
                $this->val = null;
                $this->is_empty = true;
            }
            return $this;
        }

        /**
         * Remove any unregular chars (A-Z0-9\._-)
         *
         * @return input_model
         */
        public function normalize() {
            $this->val = strtolower(preg_replace('`[^A-Z0-9\._-]`is', '', $this->val));
            return $this;
        }

        /**
         * Remove spaces
         *
         * @return input_model
         */
        public function strip_spaces() {
            $this->val = str_replace(' ', '', $this->val);
            return $this;
        }

        /**
         * Remove all double spaces
         *
         * @return input_model
         */
        public function strip_double_spaces() {
            $this->val = preg_replace(
                [
                    '`[\r]`',
                    '`([ \t]{2,})`',
                    '`(([ \t]*)\n([ \t]*))+`',
                ],
                [
                    '',
                    ' ',
                    "\n",
                ],
                $this->val
            );
            return $this;
        }

        /**
         * Trim the string
         *
         * @return input_model
         */
        public function trim() {
            $this->val = trim($this->val);
            return $this;
        }

        /**
         * Shorten the string
         *
         * @param $length
         *
         * @return input_model
         */
        public function clip($length) {
            $this->val = substr($this->val, 0, $length);
            return $this;
        }

        /**
         * To lower
         *
         * @return input_model
         */
        public function tolower() {
            $this->val = strtolower($this->val);
            return $this;
        }

        /**
         * To Upper
         *
         * @return input_model
         */
        public function toupper() {
            $this->val = strtoupper($this->val);
            return $this;
        }

        /**
         * Round a number
         *
         * @param $precision
         *
         * @return input_model
         */
        public function round($precision) {
            $this->val = round(floatval($this->val), intval($precision));
            return $this;
        }

        /**
         * Replace in a string (case sensitive)
         *
         * @param string|array $search
         * @param string|array $replace
         *
         * @return input_model
         */
        public function replace($search, $replace='') {
            $this->val = str_replace($search, $replace, $this->val);
            return $this;
        }

        /**
         * Replace in a string (case insensitive)
         *
         * @param string|array $search
         * @param string|array $replace
         *
         * @return input_model
         */
        public function ireplace($search, $replace='') {
            $this->val = str_ireplace($search, $replace, $this->val);
            return $this;
        }

        /**
         * Regex replace in a string
         *
         * @param string $regex
         * @param string $replace
         *
         * @return input_model
         */
        public function replace_regex($regex, $replace='') {
            $this->val = preg_replace($regex, $replace, $this->val);
            return $this;
        }

        /**
         * Forces/Pads a decimal number
         *
         * @param $precision
         *
         * @return input_model
         */
        public function decimal($precision) {
            //only apply decimals IF they're not ".00"
            $floatval = floatval($this->val);
            $intval   = intval($this->val);

            if ($this->val !== null && $intval != $floatval) {
                //check if the rounded version is the same
                if (round($floatval, intval($precision)) == $intval) {
                    $this->val = $intval;
                } else {
                    $this->val = number_format($floatval, intval($precision));
                }
            }
            return $this;
        }

        /**
         * Not sure what this does or if its used anywhere
         *
         * @param $func
         *
         * @return input_model
         */
        public function cleanse($func) {
            $func(array_shift(func_get_args()));
            return $this;
        }



        //
        // Validators
        //

        /**
         * Optional must ALWAYS be called before any other validation, as the rules are considered required until
         * this function is run.
         *
         * @param bool $nullify
         *
         * @return input_model
         */
        public function optional($nullify=true) {
            if ($nullify) {
                $this->nullify();
            }
            $this->optional = true;
            return $this;
        }

        /**
         * Can the validation be skipped
         *
         * @return bool
         */
        public function optional_skip() {
            return $this->optional && ($this->error || $this->is_empty);
        }

        /**
         * Is the value empty
         *
         * @return bool
         */
        public function is_empty() {
            return (bool) $this->is_empty;
        }

        /**
         * Is the string long enough
         *
         * @param int|null $min
         * @param int|null $max
         *
         * @return input_model
         */
        public function length($min=null, $max=null) {
            if (! $this->error && (! $this->optional || ! $this->is_empty)) {
                $len = strlen((string) $this->val);
                if ($min && $min === $max && $len !== $min) {
                    if ($len) {
                        $this->errors($min . " char" . ($min === 1 ? '' : 's') . " required");
                    } else {
                        $this->errors('required');
                    }
                } else if ($min && $len < $min) {
                    if ($len) {
                        $this->errors($min . " char" . ($min === 1 ? '' : 's') . " minimum");
                    } else {
                        $this->errors('required');
                    }
                } else if ($max && $len > $max) {
                    $this->errors($max . " char" . ($max === 1 ? '' : 's') . " maximum");
                }
            }
            return $this;
        }

        /**
         * Digit is a certain value
         *
         * @param int $min
         * @param int $max
         *
         * @return input_model
         */
        public function digit($min, $max) {
            if (! $this->error && (! $this->optional || ! $this->is_empty)) {
                $float = (float) $this->val;
                if ($min && $float < $min) {
                    $this->errors("must be at least " . $min);
                } else if ($max && $float > $max) {
                    $this->errors("must be at most " . $min);
                }
            }
            return $this;
        }

        /**
         * Value of input is in this array
         *
         * @param array $options
         * @param bool  $optional
         *
         * @return input_model
         */
        public function in(array $options, $optional=false) {
            if (! $this->error && (! $this->optional || ! $this->is_empty)) {
                if (! in_array($this->val, $options, true)) {
                    if (! $this->val) {
                        if (! $optional) {
                            $this->errors('required');
                        }
                    } else {
                        $this->errors('invalid option selected');
                    }
                }
            }
            return $this;
        }

        /**
         * Does this string match this regex
         *
         * @param string $regex
         * @param string $error
         *
         * @return input_model
         */
        public function match_regex($regex, $error) {
            if (! $this->error && (! $this->optional || ! $this->is_empty)) {
                if (! preg_match($regex, $this->val)) {
                    $this->errors($error);
                }
            }
            return $this;
        }

        public function filter($filter, $error, $options=null) {
            if (! $this->error && (! $this->optional || ! $this->is_empty)) {
                if (! filter_var($this->val, $filter, $options)) {
                    $this->errors($error);
                }
            }
            return $this;
        }

        //some standard filters
        public function is_ip() {
            return $this->filter(FILTER_VALIDATE_IP, 'invalid IP address');
        }

        public function is_float() {
            return $this->filter(FILTER_VALIDATE_FLOAT, 'invalid number (float)');
        }

        public function is_int() {
            return $this->filter(FILTER_VALIDATE_INT, 'invalid integer');
        }

        public function is_email() {
            if (! $this->error && (! $this->optional || ! $this->is_empty)) {
                if (! preg_match('`^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$`i', $this->val)) {
                    $this->errors('invalid email');
                }
            }
            return $this;
        }

        public function is_url() {
            return $this->filter(FILTER_VALIDATE_URL, 'invalid url');
        }

        public function is_numeric() {
            if (! $this->error && (! $this->optional || ! $this->is_empty)) {
                if (! is_numeric($this->val)) {
                    $this->errors('invalid number');
                }
            }
            return $this;
        }

        public function is_date() {
            if (! $this->error && (! $this->optional || ! $this->is_empty)) {
                $date = new DateTime($this->val);

                if ($this->val !== $date->format('Y-m-d')) {
                    $this->errors('invalid date');
                }
            }
            return $this;
        }

        public function is_datetime() {
            if (! $this->error && (! $this->optional || ! $this->is_empty)) {
                $date = new DateTime($this->val);

                if ($this->val !== $date->format('Y-m-d H:i:s')) {
                    $this->errors('invalid datetime');
                }
            }
            return $this;
        }
    }