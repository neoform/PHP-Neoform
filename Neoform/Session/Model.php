<?php

    namespace Neoform\Session;

    use Neoform;

    class Model {

        const HASH_LENGTH = 40;

        /**
         * @var Neoform\Request\Request
         */
        protected $request;

        /**
         * @var Neoform\Router\Config
         */
        protected $routerConfig;

        /**
         * @var Neoform\Auth\Config
         */
        protected $authConfig;

        /**
         * @var string
         */
        protected $token;

        /**
         * @var bool
         */
        protected $tokenChanged = false;

        /**
         * @var Auth
         */
        protected $auth;

        /**
         * @var Xsrf
         */
        protected $xsrf;

        /**
         * @var Flash
         */
        protected $flash;

        /**
         * @param Neoform\Request\Request $request
         * @param Neoform\Router\Config   $routerConfig
         * @param Neoform\Auth\Config     $authConfig
         * @param Neoform\Session\Config  $sessionConfig
         */
        public function __construct(Neoform\Request\Request $request, Neoform\Router\Config $routerConfig,
                                    Neoform\Auth\Config $authConfig, Neoform\Session\Config $sessionConfig) {
            $this->request       = $request;
            $this->routerConfig  = $routerConfig;
            $this->authConfig    = $authConfig;
            $this->sessionConfig = $sessionConfig;

            $this->token = $this->request->getCookies()->get(
                $this->getSessionCookieKey()
            );

            // No token, or token is malformed? Create a new one
            if (strlen($this->token) !== 40) {
                $this->tokenChanged = true;
                $this->token        = Neoform\Encrypt\Lib::rand(self::HASH_LENGTH);
            }

            $this->auth  = new Auth($this, $this->authConfig);
            $this->xsrf  = new Xsrf($this->request, $this->sessionConfig);
            $this->flash = new Flash($this, $this->sessionConfig);
        }

        /**
         * Get secret token
         *
         * @return string
         */
        public function getToken() {
            return $this->token;
        }

        /**
         * Has the token been changed
         *
         * @return bool
         */
        public function hasTokenChanged() {
            return $this->tokenChanged;
        }

        /**
         * @return Auth
         */
        public function getAuth() {
            return $this->auth;
        }

        /**
         * @return Xsrf
         */
        public function getXsrf() {
            return $this->xsrf;
        }

        /**
         * @return Flash
         */
        public function getFlash() {
            return $this->flash;
        }

        /**
         * @return string
         */
        public function getSessionCookieKey() {
            return $this->authConfig->getCookie();
        }
    }
