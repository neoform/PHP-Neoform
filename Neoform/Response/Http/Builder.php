<?php

    namespace Neoform\Response\Http;

    use Neoform\Response\Http as Model;
    use Neoform\Response\Builder as BuilderInterface;
    use Neoform\Render\View;
    use Neoform;

    class Builder implements BuilderInterface {

        const DEFAULT_RESPONSE_CODE      = 200;
        const DEFAULT_CHARACTER_ENCODING = 'utf-8';
        const DEFAULT_CONTENT_TYPE       = 'text/html';

        /**
         * @var array
         */
        protected $headers = [];

        /**
         * @var array
         */
        protected $cookies = [];

        /**
         * @var string
         */
        protected $body;

        /**
         * @var int
         */
        protected $httpResponseCode;

        /**
         * @var string
         */
        protected $encoding;

        /**
         * @var string
         */
        protected $contentType;

        /**
         * Construct
         */
        public function __construct() {
            $this->setHeader('Cache-Control', 'private, max-age=0');
            $this->httpResponseCode = self::DEFAULT_RESPONSE_CODE;
            $this->contentType      = self::DEFAULT_CONTENT_TYPE;
            $this->encoding         = self::DEFAULT_CHARACTER_ENCODING;
        }

        /**
         * Reset to factory defaults
         *
         * @return $this
         */
        public function reset() {
            $this->resetHeaders();
            $this->resetCookies();
            $this->resetBody();
            $this->resetHttpResponseCode();
            $this->resetContentType();
            $this->resetEncoding();
            return $this;
        }

        /**
         * @return $this
         */
        public function resetHeaders() {
            $this->headers = [];
            return $this;
        }

        /**
         * @return $this
         */
        public function resetCookies() {
            $this->cookies = [];
            return $this;
        }

        /**
         * @return $this
         */
        public function resetBody() {
            $this->body = null;
            return $this;
        }

        /**
         * @return $this
         */
        public function resetHttpResponseCode() {
            $this->httpResponseCode = self::DEFAULT_RESPONSE_CODE;
            return $this;
        }

        /**
         * @return $this
         */
        public function resetContentType() {
            $this->contentType = self::DEFAULT_CONTENT_TYPE;
            return $this;
        }

        /**
         * @return $this
         */
        public function resetEncoding() {
            $this->encoding = self::DEFAULT_CHARACTER_ENCODING;
            return $this;
        }

        /**
         * @param integer $code
         *
         * @return $this
         */
        public function setResponseCode($code) {
            $this->httpResponseCode = (int) $code;
            return $this;
        }

        /**
         * @param string $encoding
         *
         * @return $this
         */
        public function setEncoding($encoding) {
            $this->encoding = (string) $encoding;
            return $this;
        }

        /**
         * @param string $k
         * @param string $v
         *
         * @return $this
         */
        public function setHeader($k, $v) {
            $this->headers[$k] = $v;
            return $this;
        }

        /**
         * @param View $view
         * @param string|null $contentType
         *
         * @return $this
         */
        public function setView(View $view, $contentType=null) {
            if ($contentType !== null) {
                $this->setContentType($contentType);
            }
            $this->body = $view->render();
            return $this;
        }

        /**
         * Create a new cookie
         *
         * @param string       $key
         * @param string       $val
         * @param integer|null $ttl
         *
         * @return $this
         */
        public function setCookie($key, $val, $ttl=null) {
            $this->cookies[$key] = [
                'key' => (string) $key,
                'val' => (string) $val,
                'ttl' => (int) $ttl ?: null,
            ];
            return $this;
        }

        /**
         * Delete a cookie
         *
         * @param string $key
         *
         * @return $this
         */
        public function deleteCookie($key) {
            $this->cookies[$key] = [
                'key' => (string) $key,
                'val' => '',
                'ttl' => time() - 100000,
            ];
            return $this;
        }

        /**
         * @param string $contentType
         *
         * @return $this
         */
        public function setContentType($contentType) {
            $this->contentType = $contentType;
            $this->setHeader('Content-type', "{$contentType}; charset=\"{$this->encoding}\"");
            return $this;
        }

        /**
         * @return Model
         */
        public function build() {
            return new Model(
                $this->httpResponseCode,
                $this->headers,
                $this->cookies,
                $this->body,
                Neoform\Router\Config::get(),
                $this->cookies ? Neoform\Request\Parameters\Cookies\Config::get() : null
            );
        }

        /**
         * 301 See Other
         *
         * @param string $location
         *
         * @return Model
         */
        public static function redirect($location, $httpResponseCode=303) {
            return (new static)
                ->setResponseCode($httpResponseCode)
                ->setHeader('Location', (string) $location)
                ->build();
        }
    }