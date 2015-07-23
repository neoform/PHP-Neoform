<?php

    namespace Neoform\Session;

    use Neoform;

    class Model {

        const HASH_LENGTH = 40;

        /**
         * @var Neoform\Request\Model
         */
        protected $request;

        /**
         * @var Neoform\Http\Config
         */
        protected $httpConfig;

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
         * @param Neoform\Request\Model  $request
         * @param Neoform\Http\Config    $httpConfig
         * @param Neoform\Auth\Config    $authConfig
         * @param Neoform\Session\Config $sessionConfig
         */
        public function __construct(Neoform\Request\Model $request, Neoform\Http\Config $httpConfig,
                                    Neoform\Auth\Config $authConfig, Neoform\Session\Config $sessionConfig) {
            $this->request       = $request;
            $this->httpConfig    = $httpConfig;
            $this->authConfig    = $authConfig;
            $this->sessionConfig = $sessionConfig;

            $this->token = $this->request->getCookies()->get(
                $this->sessionCookieKey()
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
        public function sessionCookieKey() {
            return $this->authConfig->getCookie();
        }
    }
