<?php

    namespace Neoform\Config;

    use Neoform;

    abstract class Builder {

        /**
         * @var array
         */
        protected $configValues;

        /**
         * Get the default config values
         *
         * @return array
         */
        abstract protected function defaults();

        /**
         * Validate the config values
         *
         * @throws Exception
         */
        abstract public function validate();

        /**
         * Validate the config values after the config has been compiled
         *
         * @param Collection $configs
         *
         * @throws Exception
         */
        abstract public function validatePost(Collection $configs);

        /**
         * @param array $configValues
         */
        public function __construct(array $configValues=[]) {
            // Start with the default values, merge the values from the environment
            $this->configValues = array_replace_recursive($this->defaults(), $configValues);
        }

        /**
         * The key used to identify which config builder this is
         *
         * @return string
         */
        final public function getKey() {
            return get_called_class();
        }

        /**
         * Returns the config values as an array
         *
         * @return array
         */
        final protected function getArray() {
            return $this->configValues;
        }

        /**
         * Append a config to this one
         *
         * @param Builder $configBuilder
         *
         * @return $this
         */
        final public function append(Builder $configBuilder) {
            $this->configValues = array_merge_recursive($this->configValues, $configBuilder->getArray());
            return $this;
        }

        /**
         * Merge a config into this one
         *
         * @param Builder $configBuilder
         *
         * @return $this
         */
        final public function merge(Builder $configBuilder) {
            $this->configValues = array_replace_recursive($this->configValues, $configBuilder->getArray());
            return $this;
        }

        /**
         * Merge a config into this one
         *
         * @param Builder $configBuilder
         *
         * @return $this
         */
        final public function crush(Builder $configBuilder) {
            $this->configValues = array_replace($this->configValues, $configBuilder->getArray());
            return $this;
        }
    }