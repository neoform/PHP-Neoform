<?php

    namespace Neoform\Config;

    use Neoform;

    abstract class Environment {

        /**
         * Collection of config model builders
         *
         * @var Builder\Collection
         */
        protected $configBuilderCollection;

        /**
         * Dao go loading/storing config cache
         *
         * @var Dao
         */
        protected $dao;

        /**
         * A string to identify this environment
         *
         * @return string
         */
        abstract public function getName();

        /**
         * Config values/definitions go here
         */
        abstract protected function definitions();

        /**
         * @param Dao $dao
         */
        final public function setDao(Dao $dao) {
            $this->dao = $dao;
        }

        /**
         * Build config cache based on definitions/defaults
         */
        final public function buildCache() {

            // Store the results in cache
            $this->dao->set($this->buildConfigCollection());
        }

        /**
         * Get a config file
         *
         * @param string $configName
         *
         * @return Model
         * @throws Exception
         */
        final public function getConfig($configName) {
            try {
                return $this->dao->get($configName);
            } catch (Exception $e) {

                // Cache failed to read from cache, load from source
                $configCollection = $this->buildConfigCollection();

                // Store the results in cache
                $this->dao->set($configCollection);

                return $configCollection->get($configName);
            }
        }

        /**
         * Generate/Compile the config models into a collection from the environment defaults/definitions
         *
         * @return Collection
         */
        protected function buildConfigCollection() {
            // Create a collection for our config builders
            $this->configBuilderCollection = new Builder\Collection;

            // Load the definitions set in the environment config
            $this->definitions();

            // Validate the values (round 1)
            $this->validate();

            // Generate config models from the builders
            $configCollection = $this->configBuilderCollection->build();

            // Validate the values allowing for cross dependency validation (round 2)
            // This is useful for doing in depth validation that might require the configs from another config model
            $this->validatePost($configCollection);

            return $configCollection;
        }

        /**
         * Pre compile validation
         */
        protected function validate() {
            // Run post compile validation
            foreach ($this->configBuilderCollection as $configBuilder) {
                $configBuilder->validate();
            }
        }

        /**
         * Post compile validation
         */
        protected function validatePost(Collection $configCollection) {
            // Run post compile validation
            foreach ($this->configBuilderCollection as $configBuilder) {
                $configBuilder->validatePost($configCollection);
            }
        }

        /**
         * Append a config value along side the default value (this is not commonly used)
         *
         * @param Builder $config
         *
         * @return $this
         */
        protected function append(Builder $config) {
            // If a previous config exists, merge new data into it
            if ($this->configBuilderCollection->exists($config->getKey())) {
                $this->configBuilderCollection->get($config->getKey())->append($config);
            } else {
                $this->configBuilderCollection->add($config);
            }

            return $this;
        }

        /**
         * Replace existing values within an array. (full arrays are not replaced, merely their scalar values)
         *
         * @param Builder $config
         *
         * @return $this
         */
        protected function merge(Builder $config) {
            // If a previous config exists, merge new data into it
            if ($this->configBuilderCollection->exists($config->getKey())) {
                $this->configBuilderCollection->get($config->getKey())->merge($config);
            } else {
                $this->configBuilderCollection->add($config);
            }

            return $this;
        }

        /**
         * Replace using array_replace on the values in the config
         *
         * @param Builder $config
         *
         * @return $this
         */
        protected function crush(Builder $config) {
            // Crush previous values in a config
            if ($this->configBuilderCollection->exists($config->getKey())) {
                $this->configBuilderCollection->get($config->getKey())->crush($config);
            } else {
                $this->configBuilderCollection->add($config);
            }

            return $this;
        }

        /**
         * Replace all values in this config
         *
         * @param Builder $config
         *
         * @return $this
         */
        protected function replace(Builder $config) {
            $this->configBuilderCollection->add($config);

            return $this;
        }
    }