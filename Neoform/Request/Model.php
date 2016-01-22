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
    class Model implements Request {

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
         * @var Session\Model
         */
        protected $session;

        /**
         * @param BaseUrl                 $baseUrl
         * @param Path                    $path
         * @param Parameters\Get          $get
         * @param Parameters\Post         $post
         * @param Parameters\Files        $files
         * @param Parameters\Server       $server
         * @param Parameters\Cookies      $cookies
         * @param Session\Model           $session
         * @param Parameters\Payload|null $payload
         * @param Parameters\HttpHeaders  $httpHeaders
         */
        public function __construct(
            BaseUrl $baseUrl,
            Path $path,
            Parameters\Get $get,
            Parameters\Post $post,
            Parameters\Files $files,
            Parameters\Server $server,
            Parameters\Cookies $cookies,
            Session\Model $session=null,
            Parameters\Payload $payload=null,
            Parameters\HttpHeaders $httpHeaders=null
        ) {
            $this->baseUrl     = $baseUrl;
            $this->path        = $path;
            $this->get         = $get;
            $this->post        = $post;
            $this->files       = $files;
            $this->server      = $server;
            $this->cookies     = $cookies;
            $this->session     = $session;
            $this->payload     = $payload;
            $this->httpHeaders = $httpHeaders;
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
         * @return Parameters\Payload
         */
        public function getPayload() {
            return $this->payload;
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
         * @return Parameters\Server
         */
        public function getHttpHeaders() {
            return $this->httpHeaders;
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
            return $this->path->getControllerSlugs();
        }

        /**
         * Delegate function
         *
         * @return Parameters\Slugs
         */
        public function getNonControllerSlugs() {
            return $this->path->getNonControllerSlugs();
        }
    }