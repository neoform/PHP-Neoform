<?php

    namespace Neoform\Request;

    use Neoform\Locale;
    use Neoform\Router;
    use Neoform\Session;
    use Neoform;

    /**
     * Class Model
     *
     * @package Neoform\Request
     */
    class Model {

        /**
         * @var array
         */
        protected $config;

        /**
         * @var BaseUrl
         */
        protected $baseUrl;

        /**
         * @var Path
         */
        protected $path;

        /**
         * @var Parameters\Get
         */
        protected $get;

        /**
         * @var Parameters\Post
         */
        protected $post;

        /**
         * @var Parameters\Files
         */
        protected $files;

        /**
         * @var Parameters\Server
         */
        protected $server;

        /**
         * @var Parameters\Cookies
         */
        protected $cookies;

        /**
         * @var Session\Model
         */
        protected $session;

        /**
         * @var Parameters\Slugs
         */
        protected $nonControllerSlugs;

        /**
         * @var Parameters\Slugs
         */
        protected $controllerSlugs;

        /**
         * @param BaseUrl $baseUrl
         * @param Path $path
         * @param Parameters\Get $get
         * @param Parameters\Post $post
         * @param Parameters\Files $files
         * @param Parameters\Server $server
         * @param Parameters\Cookies $cookies
         */
        public function __construct(
            BaseUrl $baseUrl,
            Path $path,
            Parameters\Get $get,
            Parameters\Post $post,
            Parameters\Files $files,
            Parameters\Server $server,
            Parameters\Cookies $cookies
        ) {
            $this->baseUrl = $baseUrl;
            $this->path    = $path;
            $this->get     = $get;
            $this->post    = $post;
            $this->files   = $files;
            $this->server  = $server;
            $this->cookies = $cookies;

            // Run this last
            $this->session = new Session\Model(
                $this,
                Neoform\Http\Config::get(),
                Neoform\Auth\Config::get(),
                Neoform\Session\Config::get()
            );
        }

        /**
         * @return BaseUrl
         */
        public function getBaseUrl() {
            return $this->baseUrl;
        }

        /**
         * @return Path
         */
        public function getPath() {
            return $this->path;
        }

        /**
         * @return Parameters\Get
         */
        public function getGet() {
            return $this->get;
        }

        /**
         * @return Parameters\Post
         */
        public function getPost() {
            return $this->post;
        }

        /**
         * @return Parameters\Files
         */
        public function getFiles() {
            return $this->files;
        }

        /**
         * @return Parameters\Server
         */
        public function getServer() {
            return $this->server;
        }

        /**
         * @return Parameters\Cookies
         */
        public function getCookies() {
            return $this->cookies;
        }

        /**
         * @return Session\Model
         */
        public function getSession() {
            return $this->session;
        }

        /**
         * Delegate function
         *
         * @return Parameters\Parameters
         */
        public function getParameters() {
            return $this->path->getSlugs()->getParameters();
        }

        /**
         * Delegate function
         *
         * @return Parameters\Slugs
         */
        public function getSlugs() {
            return $this->path->getSlugs();
        }

        /**
         * Delegate function
         *
         * @return Parameters\Slugs
         */
        public function getControllerSlugs() {
            return $this->controllerSlugs;
        }

        /**
         * Delegate function
         *
         * @return Parameters\Slugs
         */
        public function getNonControllerSlugs() {
            return $this->nonControllerSlugs;
        }

        /**
         * Once the router has determined some things about the request, apply that info to the request
         *
         * eg, what the controller slugs are
         *
         * @param Router\Model $router
         */
        public function applyRouter(Router\Model $router) {
            $this->nonControllerSlugs = $router->getNonControllerSlugs();
            $this->controllerSlugs    = $router->getControllerSlugs();
        }
    }