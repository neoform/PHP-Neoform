<?php

    namespace Neoform\Request;

    use Neoform\Session;

    interface Request {

        /**
         * @return BaseUrl
         */
        public function getBaseUrl();

        /**
         * @return Path
         */
        public function getPath();

        /**
         * @return Parameters\Get
         */
        public function getGet();

        /**
         * @return Parameters\Post
         */
        public function getPost();

        /**
         * @return Parameters\Payload
         */
        public function getPayload();

        /**
         * @return Parameters\Files
         */
        public function getFiles();

        /**
         * @return Parameters\Server
         */
        public function getServer();

        /**
         * @return Parameters\Server
         */
        public function getHttpHeaders();

        /**
         * @return Parameters\Cookies
         */
        public function getCookies();

        /**
         * @return Session\Model
         */
        public function getSession();
    }