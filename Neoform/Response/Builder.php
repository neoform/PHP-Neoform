<?php

    namespace Neoform\Response;

    use Neoform;

    interface Builder {

        /**
         * Reset's the response object back to defaults
         * @return $this
         */
        public function reset();

        /**
         * @return $this
         */
        public function resetHeaders();

        /**
         * @return $this
         */
        public function resetCookies();

        /**
         * @return $this
         */
        public function resetBody();

        /**
         * @return $this
         */
        public function resetHttpResponseCode();

        /**
         * @return $this
         */
        public function resetContentType();

        /**
         * @return $this
         */
        public function resetEncoding();

        /**
         * @param integer $code
         * @return $this
         */
        public function setResponseCode($code);

        /**
         * @param $encoding
         * @return $this
         */
        public function setEncoding($encoding);

        /**
         * @param string $k
         * @param string $v
         * @return $this
         */
        public function setHeader($k, $v);

        /**
         * @param Neoform\Render\View $view
         * @param string $contentType
         * @return $this
         */
        public function setView(Neoform\Render\View $view, $contentType = null);

        /**
         * @param string       $key
         * @param string       $val
         * @param integer|null $ttl
         * @return $this
         */
        public function setCookie($key, $val, $ttl=null);

        /**
         * @param string $key
         * @return $this
         */
        public function deleteCookie($key);

        /**
         * @param string $contentType
         * @return $this
         */
        public function setContentType($contentType);

        /**
         * @return Response
         */
        public function build();
    }