<?php

    namespace Neoform\Request\Parameters;

    use Neoform\Request;

    /**
     * Class Server
     *
     * @package Neoform\Request\Parameters
     */
    class Server extends Request\Parameters {

        /**
         * @var bool
         */
        protected $isHttps;

        /**
         * @var string
         */
        protected $domain;

        /**
         * @var string
         */
        protected $domainRoot;

        /**
         * @var string
         */
        protected $subdomain;

        /**
         * @var string
         */
        protected $agent;

        /**
         * @var string
         */
        protected $ip;

        /**
         * @var string
         */
        protected $method;

        /**
         * @var string
         */
        protected $query;

        /**
         * @var array
         */
        protected $accepts;

        /**
         * @var string
         */
        protected $url;

        /**
         * @var string
         */
        protected $uri;

        /**
         * @var string
         */
        protected $scriptPath;

        /**
         * @var string|null
         */
        protected $referer;

        /**
         * @var string|null
         */
        protected $date;

        /**
         * @var string|null
         */
        protected $authorization;

        /**
         * @var bool|null
         */
        protected $isHotlinked;

        /**
         * @var bool|null
         */
        protected $isReferredInternally;

        /**
         * @param array $vals
         */
        public function __construct(array $vals) {
            parent::__construct($vals);

            $this->domain = isset($this->vals['SERVER_NAME']) ? strtolower($this->vals['SERVER_NAME']) : '';

            if (preg_match('`^(?:([a-z0-9\.\-_]*)\.)?([a-z][a-z0-9\-_]+\.[a-z]{2,})$`i', $this->domain, $match)) {
                $this->subdomain  = $match[1];
                $this->domainRoot = $match[2];
            }

            if (isset($this->vals['HTTP_ACCEPT'])) {
                if (preg_match('`^([^;]+)`', $this->vals['HTTP_ACCEPT'], $match)) {
                    $acceptsEncoding = preg_split('`\s*,\s*`', $match[1], -1, PREG_SPLIT_NO_EMPTY);
                } else {
                    $acceptsEncoding = [ $this->vals['HTTP_ACCEPT'], ];
                }
            } else {
                $acceptsEncoding = [];
            }

            $this->accepts       = new Server\AcceptsEncoding($acceptsEncoding);
            $this->query         = isset($this->vals['QUERY_STRING']) ? $this->vals['QUERY_STRING'] : null;
            $this->uri           = isset($this->vals['REQUEST_URI']) ? $this->vals['REQUEST_URI'] : null;
            $this->isHttps       = isset($this->vals['HTTPS']) && strtolower($this->vals['HTTPS']) === 'on';
            $this->agent         = isset($this->vals['HTTP_USER_AGENT']) ? $this->vals['HTTP_USER_AGENT'] : null;
            $this->ip            = isset($this->vals['REMOTE_ADDR']) ? $this->vals['REMOTE_ADDR'] : null;
            $this->method        = isset($this->vals['REQUEST_METHOD']) ? $this->vals['REQUEST_METHOD'] : null;
            $this->referer       = isset($this->vals['HTTP_REFERER']) ? $this->vals['HTTP_REFERER'] : null;
            $this->date          = isset($this->vals['DATE']) ? $this->vals['DATE'] : null;
            $this->authorization = isset($this->vals['HTTP_AUTHORIZATION']) ? $this->vals['HTTP_AUTHORIZATION'] : null;
            $this->scriptPath    = isset($this->vals['SCRIPT_NAME']) ? dirname($this->vals['SCRIPT_NAME']) : null;
            $this->url           = ($this->isHttps ? 'https://' : 'http://') . $this->domain . $this->uri . ($this->query ? "?{$this->query}" : '');
        }

        /**
         * @return boolean
         */
        public function isHttps() {
            return $this->isHttps;
        }

        /**
         * @return string
         */
        public function getDomain() {
            return $this->domain;
        }

        /**
         * @return string
         */
        public function getDomainRoot() {
            return $this->domainRoot;
        }

        /**
         * @return string
         */
        public function getSubdomain() {
            return $this->subdomain;
        }

        /**
         * @return string
         */
        public function getAgent() {
            return $this->agent;
        }

        /**
         * @return string
         */
        public function getIp() {
            return $this->ip;
        }

        /**
         * @return string
         */
        public function getMethod() {
            return $this->method;
        }

        /**
         * @return string
         */
        public function getQuery() {
            return $this->query;
        }

        /**
         * @return Server\AcceptsEncoding
         */
        public function getAccepts() {
            return $this->accepts;
        }

        /**
         * @return string
         */
        public function getUrl() {
            return $this->url;
        }

        /**
         * @return string
         */
        public function getUri() {
            return $this->uri;
        }

        /**
         * @return string
         */
        public function getScriptPath() {
            return $this->scriptPath;
        }

        /**
         * @return string|null
         */
        public function getReferer() {
            return $this->referer;
        }

        /**
         * @return string|null
         */
        public function getDate() {
            return $this->date;
        }

        /**
         * @return string|null
         */
        public function getAuthorization() {
            return $this->authorization;
        }

        /**
         * Returns true if this request was hotlinked
         *
         * @return bool
         */
        public function isHotlinked() {
            // Lazy loaded
            if ($this->isHotlinked === null) {
                try {
                    if ($referer = trim($this->referer)) {
                        $ref = parse_url(strtolower($referer));
                        if (isset($ref['host']) && preg_match('`^(?:([a-z0-9\.\-_]*)\.)?([a-z][a-z0-9\-_]+\.[a-z]{2,})$`i', $ref['host'], $match)) {
                            return $this->isHotlinked = $match[2] !== $this->domainRoot;
                        }
                    }

                } catch (\Exception $e) {

                }

                // No referral or bad referral = it's been hotlinked
                $this->isHotlinked = true;
            }

            return $this->isHotlinked;
        }

        /**
         * Is this request referred
         *
         * @return bool
         */
        public function isReferredInternally() {
            // Lazy loaded
            if ($this->isReferredInternally === null) {

                try {
                    // Make sure the referral domain matches this request
                    if ($referer = trim($this->referer)) {
                        $referer       = parse_url(strtolower($referer));
                        $refererDomain = isset($referer['host']) ? $referer['host'] : false;

                        // Direct match to the domain or the domain root
                        if ($refererDomain === $this->domain || $refererDomain === $this->domainRoot) {
                            return $this->isReferredInternally = true;
                        }

                        // Domain contains the domain root
                        if (preg_match('`\.' . preg_quote($this->domainRoot) . '$`i', $refererDomain)) {
                            return $this->isReferredInternally = true;
                        }
                    }
                } catch (\Exception $e) {

                }

                $this->isReferredInternally = false;
            }

            return $this->isReferredInternally;
        }
    }