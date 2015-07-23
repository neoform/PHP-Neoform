<?php

    namespace Neoform\Request;

    use Neoform;

    /**
     * Class Path
     *
     * @package Neoform\Request
     */
    class Path {

        /**
         * @var string
         */
        protected $path;

        /**
         * @var string
         */
        protected $uri;

        /**
         * @var string
         */
        protected $query;

        /**
         * @var Parameters\Slugs
         */
        protected $slugs;

        /**
         * @var bool
         */
        protected $hasTrailingSlash;

        /**
         * @param string $path
         * @param Neoform\Locale\Config $localeConfig
         * @throws Exception
         */
        public function __construct($path, Neoform\Locale\Config $localeConfig) {

            if (! is_string($path)) {
                throw new Exception('Path must be a string');
            }

            if (! trim($path)) {
                throw new Exception('Path cannot be empty');
            }

            // Strip off any GET elements
            if (strpos($path, '?') !== false && preg_match('`^([^\?]*)\?(.*?)$`', $path, $match)) {
                $path = $match[1];
            }

            $this->path = $path;

            $this->hasTrailingSlash = substr($this->path, -1) === '/';
            $this->path             = trim($this->path, '/');

            // Get slugs from path
            $slugs = $this->path ? explode('/', $this->path) : [];

            // Root slug is a slash, even if it's not there
            array_unshift($slugs, '/');

            $this->slugs = new Parameters\Slugs($slugs);
            $this->slugs->extractLocale(1, $localeConfig->getAllowed());

            // Re-apply the slash at the beginning
            $this->path = "/{$this->path}";
        }

        /**
         * @return Parameters\Slugs
         */
        public function getSlugs() {
            return $this->slugs;
        }

        /**
         * @return string
         */
        public function __toString() {
            return $this->path;
        }

        /**
         * @return bool
         */
        public function hasTrailingSlash() {
            return $this->hasTrailingSlash;
        }

        /**
         * @return Parameters\Parameters
         */
        public function getParameters() {
            return $this->slugs->getParameters();
        }
    }