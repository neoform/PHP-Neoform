<?php

    namespace Neoform\Input;

    use DateTime;

    class Model implements Input {

        /**
         * @var float|int|null|string
         */
        protected $val;

        /**
         * @var float|int|null|string
         */
        protected $defaultVal;

        /**
         * @var string
         */
        protected $error;

        /**
         * @var array
         */
        protected $data = [];

        /**
         * @var bool
         */
        protected $isOptional = false;

        /**
         * @var bool
         */
        protected $isEmpty;

        /**
         * @var bool
         */
        protected $exists;

        /**
         * @var bool
         */
        protected $isValidated = false;

        /**
         * @param string|int|float|null $val
         * @param bool                  $exists
         */
        public function __construct($val=null, $exists=true) {
            $this->val     = $val;
            $this->isEmpty = $this->val === null || ! (bool) strlen((string) $this->val);
            $this->exists  = $exists;
        }

        /**
         * @return string
         */
        public function __toString() {
            return (string) $this->val;
        }

        /**
         * Dummy function
         *
         * @param int|null $min
         * @param int|null $max
         *
         * @return $this
         */
        public function requireCount($min, $max) {
            $this->error = 'Invalid type';
            return $this;
        }

        /**
         * Does this entry exist
         *
         * @return bool
         */
        public function exists() {
            return (bool) $this->exists;
        }

        /**
         * Set value
         *
         * @param $v
         *
         * @return $this
         */
        public function setVal($v) {
            $this->val     = $v;
            $this->exists  = true;
            $this->isEmpty = $this->val === null || ! (bool) strlen(trim((string) $this->val));
            return $this;
        }

        /**
         * Unset value
         *
         * @return $this
         */
        public function unSetVal() {
            $this->val     = null;
            $this->exists  = false;
            $this->isEmpty = true;
        }

        /**
         * Unset if value is empty
         *
         * @return $this
         */
        public function unSetValIfEmpty() {
            if ($this->val === null || ! (bool) strlen(trim((string) $this->val))) {
                $this->unSetVal();
            }
            return $this;
        }

        /**
         * Reset local errors
         *
         * @return $this
         */
        public function resetErrors() {
            $this->error = null;
            return $this;
        }

        /**
         * Get this object
         *
         * @return $this
         */
        public function get() {
            return $this;
        }

        /**
         * Sets temporary local data
         *
         * @param string $k key
         * @param mixed  $v value
         *
         * @return $this
         */
        public function setData($k, $v) {
            $this->data[$k] = $v;
            return $this;
        }

        /**
         * Sets temporary local data
         *
         * @param string $k key
         *
         * @return mixed
         */
        public function getData($k) {
            if (isset($this->data[$k])) {
                return $this->data[$k];
            }
        }

        /**
         * Value of the input
         *
         * @return float|int|string
         */
        public function getVal() {
            return $this->isEmpty && $this->isOptional ? null : $this->val;
        }

        /**
         * Default value, if exists
         *
         * @return float|int|string
         */
        public function getDefaultVal() {
            return $this->defaultVal;
        }

        /**
         * Is this input valid
         *
         * @return bool
         */
        public function isValid() {
            return ! $this->error;
        }

        /**
         * Has this input been validated
         *
         * @return bool
         */
        public function isValidated() {
            return $this->isValidated;
        }

        /**
         * Is this input optional
         *
         * @return bool
         */
        public function isOptional() {
            return $this->isOptional;
        }

        /**
         * Is this input empty
         *
         * @return bool
         */
        public function isEmpty() {
            return $this->isEmpty;
        }

        /**
         * Is this a collection
         *
         * @return bool
         */
        public function isCollection() {
            return false;
        }

        /**
         * Return errors if any
         *
         * @param string $error
         *
         * @return $this
         */
        public function setErrors($error) {
            $this->error = $error;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getErrors() {
            return $this->error;
        }

        /**
         * Returns new Exception with errors in it
         *
         * @return Exception
         */
        public function getException() {
            return new Exception(new Error\Collection([
                $this->getErrors()
            ]));
        }

        /**
         * Transfer any errors from one input to another
         *
         * @param Input $input
         *
         * @return $this
         */
        public function transferErrorsTo(Input $input) {
            if ($this->getErrors() && ! $input->getErrors()) {
                $input->setErrors($this->getErrors());
                $this->resetErrors();
            }
            return $this;
        }

        /**
         * @param callable $func
         *
         * @return $this
         */
        public function each(callable $func) {
            $this->error = 'Invalid type';
            return $this;
        }

        /**
         * Dummy function
         *
         * @return $this
         */
        public function forceUnique() {
            $this->error = 'Invalid type';
            return $this;
        }

        /**
         * Run a callback on this input
         *
         * @param callable $func
         *
         * @return $this
         */
        public function callback(callable $func) {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            $func($this);
            return $this;
        }

        //
        // Modifiers
        //

        /**
         * @param string $type
         * @param bool   $strict
         *
         * @return $this
         */
        public function requireTypeCast($type, $strict=false) {
            // Cannot be an array or a non-scalar (null is not a scalar, but is allowed)
            if (is_array($type) || ($this->val !== null && ! is_scalar($this->val))) {
                $this->error = 'Invalid type';
                return $this;
            }

            if ($this->error) {
                return $this;
            }

            /**
             * Cast a value. When strict mode is off, strings that are equal to "null", "undefined", "true", "false"
             * are converted to their proper type. This is useful because some browsers are stupid and will pass these
             * values as strings instead of the literals they are.
             */
            if (! $strict) {
                $v = strtolower($this->val);
                if ($v === 'true') {
                    $this->val = true;
                } else if ($v === 'false') {
                    $this->val = false;
                } else if ($v === 'null' || $v === 'undefined') {
                    $this->val     = null;
                    $this->isEmpty = true;
                }
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            switch ($type) {
                case 'bool':
                case 'boolean':
                    $this->val = (bool) $this->val;
                    break;

                case 'str':
                case 'string':
                    // No additional validation, since everything (scalars at least) can be a string in PHP...
                    $this->val = (string) $this->val;
                    break;

                case 'int':
                case 'integer':
                    if (! is_int($this->val) && ! ctype_digit((string) $this->val)) {
                        $this->setErrors('Invalid number');
                        break;
                    }
                    $this->val = (int) $this->val;
                    break;

                case 'float':
                    if (! is_float($this->val + 0) && ! is_int($this->val) && ! ctype_digit((string) $this->val)) {
                        $this->setErrors('Invalid number');
                        break;
                    }
                    $this->val = (float) $this->val;
                    break;

                case 'number':
                    if (! is_numeric($this->val)) {
                        $this->setErrors('Invalid number');
                        break;
                    }
                    $this->val = (float) preg_replace('`[^0-9\.\-]`is', '', $this->val);
                    break;
            }

            return $this;
        }

        /**
         * Mark the input as having been validated
         *
         * @return $this
         */
        public function markAsValidated() {
            $this->isValidated = true;
            return $this;
        }

        /**
         * Remove any unregular chars (A-Z0-9\._-)
         *
         * @return $this
         */
        public function normalize() {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            $this->val = strtolower(preg_replace('`[^A-Z0-9\._-]`is', '', $this->val));
            return $this;
        }

        /**
         * Remove any unregular chars (A-Z0-9\._-)
         *
         * @return $this
         */
        public function slug($maxLength) {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            // word safe chopping
            if (strlen($this->val) > $maxLength) {
                $this->val = substr($this->val, 0, strpos(wordwrap($this->val, $maxLength), "\n"));
            }

            $this->val = preg_replace(
                '`[\.]{2,}`', '.',
                preg_replace(
                    '`[\s\-]+`', '-',
                    preg_replace(
                        '`[^a-z0-9\.\-_\s]`',
                        '',
                        strtolower($this->val)
                    )
                )
            );
            $this->val = trim($this->val, "-._ \t\n\r\0\x0B");

            return $this;
        }

        /**
         * Remove spaces
         *
         * @return $this
         */
        public function stripSpaces() {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            $this->val = str_replace(' ', '', $this->val);
            return $this;
        }

        /**
         * Remove all double spaces
         *
         * @return $this
         */
        public function stripDoubleSpaces() {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

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
         * @param string|null $charList
         *
         * @return $this
         */
        public function trim($charList=null) {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            $this->val = $charList !== null ? trim($this->val, $charList) : trim($this->val);
            return $this;
        }

        /**
         * Shorten the string
         *
         * @param $length
         *
         * @return $this
         */
        public function clip($length) {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            $this->val = substr($this->val, 0, $length);
            return $this;
        }

        /**
         * To lower
         *
         * @return $this
         */
        public function toLower() {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            $this->val = strtolower($this->val);
            return $this;
        }

        /**
         * To Upper
         *
         * @return $this
         */
        public function toUpper() {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            $this->val = strtoupper($this->val);
            return $this;
        }

        /**
         * Round a number
         *
         * @param $precision
         *
         * @return $this
         */
        public function round($precision) {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            $this->val = round(floatval($this->val), intval($precision));
            return $this;
        }

        /**
         * Replace in a string (case sensitive)
         *
         * @param string|array $search
         * @param string|array $replace
         *
         * @return $this
         */
        public function replace($search, $replace='') {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            $this->val = str_replace($search, $replace, $this->val);
            return $this;
        }

        /**
         * Replace in a string (case insensitive)
         *
         * @param string|array $search
         * @param string|array $replace
         *
         * @return $this
         */
        public function iReplace($search, $replace='') {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            $this->val = str_ireplace($search, $replace, $this->val);
            return $this;
        }

        /**
         * Regex replace in a string
         *
         * @param string $regex
         * @param string $replace
         *
         * @return $this
         */
        public function replaceRegex($regex, $replace='') {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            $this->val = preg_replace($regex, $replace, $this->val);
            return $this;
        }

        /**
         * Forces/Pads a decimal number
         *
         * @param $precision
         *
         * @return $this
         */
        public function forceDecimal($precision) {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            // Only apply decimals IF they're not ".00"
            $floatVal = (float) $this->val;
            $intVal   = (int) $this->val;

            if ($this->val !== null && $intVal != $floatVal) {
                //check if the rounded version is the same
                if (round($floatVal, intval($precision)) == $intVal) {
                    $this->val = $intVal;
                } else {
                    $this->val = number_format($floatVal, intval($precision));
                }
            }
            return $this;
        }

        /**
         * Optional must ALWAYS be called before any other validation, as the rules are considered required until
         * this function is run.
         *
         * @param mixed|null $defaultVal
         *
         * @return $this
         */
        public function markAsOptional($defaultVal=null) {
            $this->isOptional = true;
            $this->defaultVal = $defaultVal;
            return $this;
        }

        /**
         * Is the string long enough
         *
         * @param int|null $min
         * @param int|null $max
         *
         * @return $this
         */
        public function requireLength($min=null, $max=null) {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            $len = strlen((string) $this->val);
            if ($min && $min === $max && $len !== $min) {
                if ($len) {
                    $this->error = "{$min} char" . ($min === 1 ? '' : 's') . " required";
                } else {
                    $this->error = 'Required';
                }
            } else if ($min && $len < $min) {
                if ($len) {
                    $this->error = "{$min} char" . ($min === 1 ? '' : 's') . " minimum";
                } else {
                    $this->error = 'Required';
                }
            } else if ($max && $len > $max) {
                $this->error = "{$max} char" . ($max === 1 ? '' : 's') . " maximum";
            }

            return $this;
        }

        /**
         * Digit is a certain value
         *
         * @param int $min
         * @param int $max
         *
         * @return $this
         */
        public function requireDigit($min, $max) {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            $float = (float) $this->val;
            if ($min && $float < $min) {
                $this->error = "Must be at least {$min}";
            } else if ($max && $float > $max) {
                $this->error = "Must be at most {$max}";
            }

            return $this;
        }

        /**
         * Value of input is in this array
         *
         * @param array $options
         * @param bool  $isOptional
         *
         * @return $this
         */
        public function isIn(array $options, $isOptional=false) {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            if (! in_array($this->val, $options, true)) {
                if (! $this->val) {
                    if (! $isOptional) {
                        $this->error = 'Required';
                    }
                } else {
                    $this->error = 'Invalid option selected';
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
         * @return $this
         */
        public function matchRegex($regex, $error) {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            if (! preg_match($regex, $this->val)) {
                $this->setErrors($error);
            }

            return $this;
        }

        /**
         * Run a PHP \Filter_var()
         *
         * @param      $filter
         * @param      $error
         * @param null $options
         *
         * @return $this
         */
        public function filter($filter, $error, $options=null) {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            if (! filter_var($this->val, $filter, $options)) {
                $this->error = $error;
            }

            return $this;
        }

        /**
         * Is it an IP address
         *
         * @return $this
         */
        public function isIp() {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            return $this->filter(FILTER_VALIDATE_IP, 'Invalid IP address');
        }

        /**
         * Is it a float
         *
         * @return $this
         */
        public function isFloat() {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            return $this->filter(FILTER_VALIDATE_FLOAT, 'Invalid number (float)');
        }

        /**
         * Is it an integer
         *
         * @return $this
         */
        public function isInt() {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            return $this->filter(FILTER_VALIDATE_INT, 'Invalid number (integer)');
        }

        /**
         * Is it an email (simple regex)
         *
         * @return $this
         */
        public function isEmail() {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            if (! preg_match('`^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$`i', $this->val)) {
                $this->error = 'Invalid email';
            }

            return $this;
        }

        /**
         * Is it a URL
         *
         * @return $this
         */
        public function isUrl() {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            return $this->filter(FILTER_VALIDATE_URL, 'Invalid url');
        }

        /**
         * Is it numeric
         *
         * @return $this
         */
        public function isNumeric() {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            if (! is_numeric($this->val)) {
                $this->error = 'Invalid number';
            }

            return $this;
        }

        /**
         * Is it a valid date string
         *
         * @return Model
         */
        public function isDate() {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            if (! $this->val || ! preg_match('`^\d{4}-\d{2}-\d{2}$`', $this->val)
                || $this->val !== (new DateTime($this->val))->format('Y-m-d')) {
                $this->error = 'Invalid date';
            }

            return $this;
        }

        /**
         * Is it a valid datetime string
         *
         * @return $this
         */
        public function isDateTime() {
            if ($this->error) {
                return $this;
            }

            if ($this->isOptional && $this->isEmpty) {
                return $this;
            }

            if (! $this->val || ! preg_match('`^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}`', $this->val)
                || $this->val !== (new DateTime($this->val))->format('Y-m-d H:i:s')) {
                $this->error = 'Invalid datetime';
            }

            return $this;
        }
    }