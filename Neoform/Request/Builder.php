<?php

    namespace Neoform\Request;

    use Neoform\Locale;
    use Neoform;

    /**
     * Class Builder
     *
     * @package Neoform\Request
     */
    class Builder implements Request {

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
         * @var Parameters\Payload
         */
        protected $payload;

        /**
         * @var Parameters\Files
         */
        protected $files;

        /**
         * @var Parameters\Server
         */
        protected $server;

        /**
         * @var Parameters\HttpHeaders
         */
        protected $httpHeaders;

        /**
         * @var Parameters\Cookies
         */
        protected $cookies;

        /**
         * @var Neoform\Session\Model
         */
        protected $session;

        /**
         * @var Neoform\Router\Config
         */
        protected $config;

        /**
         * @param Neoform\Router\Config $config
         */
        public function __construct(Neoform\Router\Config $config) {
            $this->config = $config;
        }

        /**
         * @param string                     $path
         * @param Neoform\Locale\Config|null $localeConfig
         *
         * @return $this
         */
        public function setPath($path, Neoform\Locale\Config $localeConfig=null) {
            $this->path = new Path($path, $localeConfig);
            return $this;
        }

        /**
         * @return Path|null
         */
        public function getPath() {
            return $this->path;
        }

        /**
         * @param array $get
         *
         * @return $this
         */
        public function setGet(array $get) {
            $this->get = new Parameters\Get($get);
            return $this;
        }

        /**
         * @return Parameters\Get
         */
        public function getGet() {
            return $this->get;
        }

        /**
         * @param array $post
         *
         * @return $this
         */
        public function setPost(array $post) {
            $this->post = new Parameters\Post($post);
            return $this;
        }

        /**
         * @return Parameters\Post
         */
        public function getPost() {
            return $this->post;
        }

        /**
         * @param string|null $payload
         *
         * @return $this
         */
        public function setPayload($payload) {
            $this->payload = new Parameters\Payload($payload);
            return $this;
        }

        /**
         * @return Parameters\Payload
         */
        public function getPayload() {
            return $this->payload;
        }

        /**
         * @param array $files
         *
         * @return $this
         */
        public function setFiles(array $files) {
            $this->files = new Parameters\Files($files);
            return $this;
        }

        /**
         * @return Parameters\Files
         */
        public function getFiles() {
            return $this->files;
        }

        /**
         * @param array $server
         *
         * @return $this
         */
        public function setServer(array $server) {
            $this->server  = new Parameters\Server($server);
            $this->baseUrl = new BaseUrl(
                $this->server ?: new Parameters\Server([]),
                $this->config
            );
            return $this;
        }

        /**
         * @return Parameters\Server
         */
        public function getServer() {
            return $this->server;
        }

        /**
         * @return BaseUrl
         */
        public function getBaseUrl() {
            return $this->baseUrl;
        }

        /**
         * @param string[] $httpHeaders
         *
         * @return $this
         */
        public function setHttpHeaders(array $httpHeaders) {
            $this->httpHeaders = new Parameters\HttpHeaders($httpHeaders);
            return $this;
        }

        /**
         * @return Parameters\HttpHeaders
         */
        public function getHttpHeaders() {
            return $this->httpHeaders;
        }

        /**
         * @param array $cookies
         *
         * @return $this
         */
        public function setCookies(array $cookies) {
            $this->cookies = new Parameters\Cookies($cookies);
            return $this;
        }

        /**
         * @return Parameters\Cookies
         */
        public function getCookies() {
            return $this->cookies;
        }

        /**
         * @return Neoform\Session\Model
         *
         * @return $this
         */
        public function loadSession() {
            $this->session = new Neoform\Session\Model(
                $this,
                Neoform\Router\Config::get(),
                Neoform\Auth\Config::get(),
                Neoform\Session\Config::get()
            );
            return $this;
        }

        /**
         * @return Neoform\Session\Model
         */
        public function getSession() {
            return $this->session;
        }

        /**
         * Update the request builder to include data from the router
         *
         * @param Neoform\Router\Model $router
         *
         * @return $this
         */
        public function applyRouter(Neoform\Router\Model $router) {
            // Apply the controller and non-controller slugs to the path
            $this->path = $this->path->routedPath(
                $router->getControllerSlugs(),
                $router->getNonControllerSlugs()
            );

            return $this;
        }

        /**
         * Build the Request Model
         *
         * @return Model
         * @throws Exception
         */
        public function build() {

            if (! $this->server) {
                throw new Exception('Server must be set');
            }

            if (! $this->path) {
                throw new Exception('Path must be set');
            }

            return new Model(
                $this->baseUrl,
                $this->path,
                $this->get ?: new Parameters\Get([]),
                $this->post ?: new Parameters\Post([]),
                $this->files ?: new Parameters\Files([]),
                $this->server ?: new Parameters\Server([]),
                $this->cookies ?: new Parameters\Cookies([]),
                $this->session,
                $this->payload,
                $this->httpHeaders
            );
        }
    }