<?php

    namespace Neoform\Input;

    interface Input {

        /**
         * @return $this
         */
        public function get();

        /**
         * @return mixed
         */
        public function getVal();

        /**
         * @return mixed|null
         */
        public function getDefaultVal();

        /**
         * @param mixed $val
         *
         * @return $this
         */
        public function setVal($val);

        /**
         * @return $this
         */
        public function unSetVal();

        /**
         * Unset if value is empty
         *
         * @return $this
         */
        public function unSetValIfEmpty();

        /**
         * @return bool
         */
        public function exists();

        /**
         * @param string $type
         * @param bool   $strict
         *
         * @return $this
         */
        public function requireTypeCast($type, $strict=false);

        /**
         * Optional must ALWAYS be called before any other validation, as the rules are considered required until
         * this function is run.
         *
         * @param mixed|null $defaultValue
         *
         * @return $this
         */
        public function markAsOptional($defaultValue=null);

        /**
         * @param string $k
         * @param mixed  $v
         *
         * @return $this
         */
        public function setData($k, $v);

        /**
         * @param string $k
         *
         * @return mixed
         */
        public function getData($k);

        /**
         * @return bool
         */
        public function isValid();

        /**
         * @return bool
         */
        public function isOptional();

        /**
         * Is this input empty
         *
         * @return bool
         */
        public function isEmpty();

        /**
         * Is this a collection
         *
         * @return bool
         */
        public function isCollection();

        /**
         * @param string $error
         *
         * @return $this
         */
        public function setErrors($error);

        /**
         * @return string|null
         */
        public function getErrors();

        /**
         * @return $this
         */
        public function resetErrors();

        /**
         * @return Exception
         */
        public function getException();

        /**
         * @param int|null $min
         * @param int|null $max
         *
         * @return $this
         */
        public function requireCount($min, $max);

        /**
         * @param callable $func
         *
         * @return $this
         */
        public function each(callable $func);

        /**
         * Get rid of duplicates
         *
         * @return $this
         */
        public function forceUnique();

        /**
         * Run a callback on this input
         *
         * @param callable $func
         *
         * @return $this
         */
        public function callback(callable $func);

        /**
         * Has the input been validated
         *
         * @return bool
         */
        public function isValidated();

        /**
         * Mark the input as having been validated
         *
         * @return $this
         */
        public function markAsValidated();

        /**
         * Transfer any errors from one input to another
         *
         * @param Input $input
         *
         * @return $this
         */
        public function transferErrorsTo(Input $input);
    }