<?php

    namespace Neoform\Request;

    use Neoform\Locale;
    use Neoform;

    /**
     * Class Builder
     *
     * @package Neoform\Request
     */
    class Builder {

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
         * @var Neoform\Http\Config
         */
        protected $config;

        /**
         * @param Neoform\Http\Config $config
         */
        public function __construct(Neoform\Http\Config $config) {
            $this->config = $config;
        }

        /**
         * @param string                $path
         * @param Neoform\Locale\Config $localeConfig
         *
         * @return $this
         */
        public function setPath($path, Neoform\Locale\Config $localeConfig) {
            $this->path = new Path($path, $localeConfig);
            return $this;
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
         * @param array $post
         *
         * @return $this
         */
        public function setPost(array $post) {
            $this->post = new Parameters\Post($post);
            return $this;
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
         * @param array $server
         *
         * @return $this
         */
        public function setServer(array $server) {
            $this->server = new Parameters\Server($server);
            return $this;
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
         * @return Model
         */
        public function build() {
            return new Model(
                new BaseUrl(
                    $this->server,
                    $this->config
                ),
                $this->path,
                $this->get ?: new Parameters\Get([]),
                $this->post ?: new Parameters\Post([]),
                $this->files ?: new Parameters\Files([]),
                $this->server ?: new Parameters\Server([]),
                $this->cookies ?: new Parameters\Cookies([])
            );
        }
    }