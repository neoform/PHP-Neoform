<?php

    /**
     * Handle all output that goes to the browser, this includes headers
     *
     * Standard usage: core::output()
     */
    class output_instance {

        use core_instance;

        protected $http_response_code = 200;
        protected $headers = [];
        protected $body;

        const HTML = 'text/html';
        const JSON = 'application/json';
        const XML  = 'application/xml';

        protected $headers_sent = false;
        protected $output_type = self::HTML;

        public function __construct() {
            $this->header('Core', 'v0.1');
            $this->header('cache-control', 'private, max-age=0');
        }

        /**
         * Set an HTTP header
         *
         * @param string      $type
         * @param string|null $val
         *
         * @return output_instance
         */
        public function header($type, $val=null) {
            $header = $type . ($val ? ': ' . $val : '');
            $hash   = md5($header);
            if (! isset($this->headers[$hash])) {
                $this->headers[$hash] = $header;
            }
            return $this;
        }

        /**
         * Create a new cookie
         *
         * @param string      $key
         * @param string      $val
         * @param intger|null $ttl
         *
         * @return bool
         */
        public function cookie_set($key, $val, $ttl=null) {

            if ($ttl === null || ! is_numeric($ttl)) {
                $ttl = time() + core::config()->cookies['ttl'];
            }

            return setcookie(
                $key,
                base64_encode($val),
                time() + intval($ttl),
                isset(core::config()->cookies['path']) ? core::config()->cookies['path'] : core::http()->server('subdir'),
                core::config()->system['domain'],
                (bool) core::config()->cookies['secure'],
                (bool) core::config()->cookies['httponly']
            );
        }

        /**
         * Delete a cookie from browser
         *
         * @param $key name of cookie
         *
         * @return bool
         */
        public static function cookie_delete($key) {
            return setcookie(
                $key,
                '',
                time() - 100000,
                isset(core::config()->cookies['path']) ? core::config()->cookies['path'] : core::http()->server('subdir'),
                core::config()->system['domain']
            );
        }

        /**
         * Get an array containing all the HTTP headers
         *
         * @return array
         */
        public function get_headers() {
            return array_values($this->headers);
        }

        /**
         * Send all HTTP headers to browser
         *
         * @return output_instance
         */
        public function send_headers() {
            if (! $this->headers_sent) {
                $this->headers_sent = true;
                http_response_code($this->http_response_code);
                foreach ($this->headers as $header) {
                    header($header);
                }
            }
            return $this;
        }

        /**
         * Set the output body, or get it
         *
         * @param string|null $str
         *
         * @return string|output_instance
         */
        public function body($str=null) {
            if ($str === null) {
                return $this->body;
            } else {
                $this->body = (string) $str;
                return $this;
            }
        }

        /**
         * Set the content-type header
         *
         * @param string|null $type 'json', 'xml', defaults to 'html', if null passed the output type is returned
         *
         * @return output_instance|string
         */
        public function output_type($type=null) {
            if ($type !== null) {
                switch ($type) {
                    case 'json':
                        $this->output_type = self::JSON;
                        $this->header('Content-type', self::JSON . '; charset="' . core::config()->system['encoding'] . '"');
                        break;

                    case 'xml':
                        $this->output_type = self::XML;
                        $this->header('Content-type', self::XML . '; charset="' . core::config()->system['encoding'] . '"');
                        break;

                    //case 'html':
                    default:
                        $this->output_type = self::HTML;
                        $this->header('Content-type', self::HTML . '; charset="' . core::config()->system['encoding'] . '"');
                        break;
                }
                return $this;
            } else {
                return $this->output_type;
            }
        }

        /**
         * Redirect the user to a different url on the site
         *
         * @param string $url
         * @param int    $http_code
         *
         * @return output_instance
         */
        public function redirect($url='', $http_code=303) {
            $base_url = substr(core::http()->server('url'), 0, -1);
            if (substr($url, 0, 1) !== '/') {
                $this->header('Location', $base_url . core::locale()->route("/{$url}"), true, $http_code);
            } else {
                $this->header('Location', $base_url . core::locale()->route($url), true, $http_code);
            }
            return $this;
        }

        /**
         * Display an error to the user
         *
         * @param string|null $title
         * @param string|null $message
         */
        public function error($title=null, $message=null) {

            try {
                //trash anything that was going to be outputted
                while (ob_get_status() && ob_end_clean()) {

                }
            } catch (Exception $e) {

            }

            // Reset the page
            $this->headers     = [];
            $this->body     = null;

            if ($this->output_type === self::JSON) {

                $json = new render_json();
                $json->status = 'fault';

                if ($title && $message) {
                    $json->message = $title . ' - ' . $message;
                } else if ($title) {
                    $json->message = $title;
                } else {
                    $json->message = 'There was a problem generating that page.';
                }

                $json->render();

            } else {

                try {
                    $this->http_status_code(500);
                    $view                 = new render_view();
                    $view->meta_title     = 'Error';
                    $view->pre_header    = 'Error';
                    $view->header         = $title ? $title : 'Server Error';
                    $view->body         = $message ? $message : 'There was a problem generating that page.';
                    $view->render(core::config()->output['default_error_view']);

                } catch (Exception $e) {
                    $this->body = $message;
                }
            }
        }

        /**
         * Get or set HTTP status code
         *
         * @param integer|null $code if passed, changes the current http status code, if not set, returns the current code
         *
         * @return int|output_instance
         */
        public function http_status_code($code=null) {
            if ($code === null) {
                return $this->http_response_code;
            } else {
                $this->http_response_code = (int) $code;
                return $this;
            }
        }
    }