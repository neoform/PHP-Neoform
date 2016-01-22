<?php

    namespace Neoform\Response;

    use Neoform;

    class Http implements Response {

        /**
         * @var int
         */
        protected $httpResponseCode;

        /**
         * @var string[]
         */
        protected $httpHeaders = [];

        /**
         * @var array
         */
        protected $cookies = [];

        /**
         * @var string|null
         */
        protected $body;

        /**
         * @var Neoform\Router\Config
         */
        protected $routerConfig;

        /**
         * @var Neoform\Request\Parameters\Cookies\Config
         */
        protected $cookiesConfig;

        /**
         * @param integer                                        $httpResponseCode
         * @param string[]                                       $httpHeaders
         * @param array                                          $cookies
         * @param string|null                                    $body
         * @param Neoform\Router\Config                          $routerConfig
         * @param Neoform\Request\Parameters\Cookies\Config|null $cookiesConfig
         */
        public function __construct($httpResponseCode, array $httpHeaders, array $cookies = null, $body = null,
                                    Neoform\Router\Config $routerConfig,
                                    Neoform\Request\Parameters\Cookies\Config $cookiesConfig=null) {
            $this->httpResponseCode = (int) $httpResponseCode;
            $this->httpHeaders      = $httpHeaders;
            $this->cookies          = $cookies;
            $this->body             = $body;
            $this->routerConfig     = $routerConfig;
            $this->cookiesConfig    = $cookiesConfig;
        }

        /**
         * Render the output
         */
        public function render() {
            $this->sendHttpHeaders();
            $this->sendCookies();
            echo $this->body;
        }

        /**
         * Send the HTTP headers
         */
        protected function sendHttpHeaders() {
            http_response_code($this->httpResponseCode);
            foreach ($this->httpHeaders as $k => $v) {
                if ($v) {
                    header("{$k}: {$v}");
                } else {
                    header($k);
                }
            }
        }

        /**
         * Send the cookie creates/deletes
         */
        protected function sendCookies() {

            if (! $this->cookies) {
                return;
            }

            if (! $this->cookiesConfig) {
                throw new Exception('Cookies config is not active');
            }

            foreach ($this->cookies as $cookie) {

                if ($cookie['ttl'] === null || !is_numeric($cookie['ttl'])) {
                    $cookie['ttl'] = time() + $this->cookiesConfig->getTtl();
                }

                setcookie(
                    $cookie['key'],
                    base64_encode($cookie['val']),
                    time() + (int)$cookie['ttl'],
                    $this->cookiesConfig->getPath() ?: '/',
                    $this->routerConfig->getDomain(),
                    (bool) $this->cookiesConfig->isSecure(),
                    (bool) $this->cookiesConfig->isHttpOnly()
                );
            }
        }
    }