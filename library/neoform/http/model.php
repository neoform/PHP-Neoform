<?php

    namespace neoform\http;

    use neoform\locale;
    use neoform\auth;
    use neoform\output;
    use neoform;

    class model {

        /**
         * Local variables, generated by this class
         */
        protected $executed;
        protected $slugs                = [];
        protected $controller_slugs     = [];
        protected $non_controller_slugs = [];
        protected $parameter_vars       = [];
        protected $server_vars          = [];
        protected $subdomain_validated;

        /**
         * Vars gathered from HTTP header info
         */
        protected $get;
        protected $post;
        protected $files;
        protected $server;
        protected $config;
        protected $locale_config;
        protected $cookies;

        /**
         * base64 decoded version of the cookies
         */
        protected $cookies_decoded = false;

        protected $ref_code_cache;
        protected $ref_secret_cache;

        /**
         * Assemble useful information based on $this->server vars and config
         *
         * @param string $router_path
         * @param array $config
         * @param array $locale_config
         * @param array $get
         * @param array $post
         * @param array $files
         * @param array $server
         * @param array $cookies
         * @returns array
         * @throws exception
         */
        public function __construct($router_path, array $config, array $locale_config, array $get, array $post, array $files, array $server, array $cookies) {
            if ($router_path === false) {
                throw new exception('Please set the routing path');
            }

            $this->get           = $get;
            $this->post          = $post;
            $this->files         = $files;
            $this->server        = $server;
            $this->config        = $config;
            $this->locale_config = $locale_config;
            $this->cookies       = $cookies;

            // Make sure require variables are set
            $subdomains = isset($config['subdomains']) && is_array($config['subdomains']) ? $config['subdomains'] : [];

            //strip off any GET elements
            if (strpos($router_path, '?') !== false) {
                if (preg_match('`([^\?]*)\?(.*)`', $router_path, $matche)) {
                    $router_path = $matche[1];
                }
            }

            $this->slugs    = explode('/', $router_path);
            $this->slugs[0] = '/';

            $slug_count = count($this->slugs);
            $unsetted   = false;

            foreach ($this->slugs as $key => $val) {
                // If this is the last slug and it's empty, remove it (trailing slashes cause the last slug to be empty)
                if ($key == $slug_count - 1 && ! strlen($val)) {
                    unset($this->slugs[$key]);
                    $unsetted = true;
                    break;
                }

                // if locale is the first param of the url, override the default locale
                if ($key === 1 && in_array($this->slugs[$key], $this->locale_config['allowed'], true)) {
                    locale::instance()->set($this->slugs[$key]);
                    unset($this->slugs[$key]);
                    $unsetted = true;
                } else {
                    //check for variables in the slugs
                    $location = strpos($val, ':');
                    if ($location !== false) {
                        $k = substr($val, 0, $location);
                        $v = substr($val, $location + 1);

                        if (strpos($v, '|') !== false) {
                            $this->parameter_vars[$k] = explode('|', $v);
                        } else {
                            $this->parameter_vars[$k] = $v;
                        }

                        unset($this->slugs[$key]);
                        $unsetted = true;
                    }
                }
            }

            if ($unsetted) {
                $this->slugs = array_values($this->slugs);
            }

            // Site's current subdirectory (if set)
            $subdir = isset($this->server['SCRIPT_NAME']) ? dirname($this->server['SCRIPT_NAME']) : '';
            $subdir = strlen($subdir) > 1 ? $subdir.'/' : '/';

            // Domain/subdomain
            $domain    = isset($this->server['SERVER_NAME']) ? strtolower($this->server['SERVER_NAME']) : '';
            $subdomain = '';

            // The period count in the site's domain
            $real_domain_slug_count = substr_count($this->config['domain'], '.') + 1; // +1 because the period count is 1 less than the slug count

            // Since we're not sure what the domain is, pull everything after the second dot (right to left) and make that the current subdomain
            if (strpos($domain, '.') !== false) {
                $domain_slugs       = explode('.', $domain);
                $domain_slugs_count = count($domain_slugs);

                if ($domain_slugs_count > $real_domain_slug_count) {
                    // sub2.sub1.domain.com
                    $subdomain = join('.', \array_splice($domain_slugs, 0, $domain_slugs_count - 2));
                    $domain    = join('.', $domain_slugs);
                }
            }

            $https = isset($this->server['HTTPS']) && $this->server['HTTPS'] == 'on';

            // Check if subdomain is valid (in the config)
            if ($subdomain && count($subdomains)) {
                foreach ($subdomains as $subdomain_pair) {
                    if ($subdomain_pair['regular'] == $subdomain || $subdomain_pair['secure'] == $subdomain) {
                        $subdomain_regular = $subdomain_pair['regular'];
                        $subdomain_secure  = $subdomain_pair['secure'];

                        //assemble the SURL (secure url) and RURL (regular url)
                        $rurl = ($config['https']['regular'] ? 'https' : 'http') . '://' .
                                ($subdomain_pair['regular'] ? $subdomain_pair['regular'] . '.' : '' ) .
                                $config['domain'] . $subdir;

                        $surl = ($config['https']['secure']  ? 'https' : 'http') . '://' .
                                ($subdomain_pair['secure'] ? $subdomain_pair['secure'] . '.' : '') .
                                $config['domain'] . $subdir;

                        $this->subdomain_validated = true;

                        break;
                    }
                }
            }

            //default urls
            //assemble the SURL (secure url) and RURL (regular url)
            $drurl = ($config['https']['regular'] ? 'https' : 'http') . '://' .
                    ($config['subdomain_default']['regular'] ? $config['subdomain_default']['regular'] . '.' : '') .
                    $config['domain'] . $subdir;

            $dsurl = ($config['https']['secure'] ? 'https' : 'http') . '://' .
                    ($config['subdomain_default']['secure'] ? $config['subdomain_default']['secure'] . '.' : '') .
                    $config['domain'] . $subdir;


            if (! $this->subdomain_validated) {
                $subdomain_regular = $config['subdomain_default']['regular'];
                $subdomain_secure  = $config['subdomain_default']['secure'];

                $rurl = $drurl;
                $surl = $dsurl;
            }

            // Query
            $query = isset($this->server['REQUEST_URI']) ? $this->server['REQUEST_URI'] : '';

            if ($query) {
                if (substr($query, 0, strlen($subdir)) == strtolower($subdir)) {
                    $query = substr($query, strlen($subdir));
                } else {
                    $query = substr($query, 1);
                }
            }

            $this->server_vars = [
                'https'             => $https,
                'domain'            => $domain,
                'subdomain'         => $subdomain,
                'subdomain_regular' => $subdomain_regular,
                'subdomain_secure'  => $subdomain_secure,
                'query'             => $query,
                'agent'             => isset($this->server['HTTP_USER_AGENT']) ? $this->server['HTTP_USER_AGENT'] : null,
                'ip'                => isset($this->server['REMOTE_ADDR']) ? $this->server['REMOTE_ADDR'] : null,
                'method'            => isset($this->server['REQUEST_METHOD']) ? $this->server['REQUEST_METHOD'] : null,
                'url'               => $rurl,  // URL
                'rurl'              => $rurl,  // Regular URL
                'surl'              => $surl,  // Secure URL
                'durl'              => $drurl, // Default URL
                'drurl'             => $drurl, // Defualt regular URL
                'dsurl'             => $dsurl, // Defualt secure URL
                'subdir'            => $subdir,
                'referer'           => isset($this->server['HTTP_REFERER']) ? $this->server['HTTP_REFERER'] : null,
            ];
        }

        /**
         * Get named/un-named slugs from the URL. If un-named, the slugs are numbered, starting at 1
         * http://www.example.com/slug1/slug2/slug3/etc..
         *
         * @param integer|string $k
         *
         * @return string|null
         */
        public function slug($k) {
            if (isset($this->slugs[$k])) {
                return $this->slugs[$k];
            }
        }

        /**
         * http://www.example.com/slug1/slug2/slug3/etc..
         *
         * @return array of url slugs
         */
        public function slugs() {
            return $this->slugs;
        }

        /**
         * Get named/un-named slugs from the URL. If un-named, the slugs are numbered, starting at 1
         * http://www.example.com/slug1/slug2/slug3/etc..
         *
         * @param integer|string $k
         *
         * @return string|null
         */
        public function controller_slug($k) {
            if (isset($this->controller_slugs[$k])) {
                return $this->controller_slugs[$k];
            }
        }

        /**
         * http://www.example.com/slug1/slug2/slug3/etc.. if it's part of a controller
         *
         * @return array of url slugs
         */
        public function controller_slugs() {
            return $this->controller_slugs;
        }

        /**
         * Get named/un-named slugs from the URL. If un-named, the slugs are numbered, starting at 1
         * http://www.example.com/slug1/slug2/slug3/etc..
         *
         * @param integer|string $k
         *
         * @return string|null
         */
        public function non_controller_slug($k) {
            if (isset($this->non_controller_slugs[$k])) {
                return $this->non_controller_slugs[$k];
            }
        }

        /**
         * http://www.example.com/slug1/slug2/slug3/etc.. if it's part of a controller
         *
         * @return array of url slugs
         */
        public function non_controller_slugs() {
            return $this->non_controller_slugs;
        }

        /**
         * URL vars. http://example.com/var1:value1/var2:varlue2/
         *
         * @param string $key
         *
         * @return string|array|null
         */
        public function parameter($key)  {
            if (isset($this->parameter_vars[$key])) {
                return $this->parameter_vars[$key];
            }
        }

        /**
         * URL vars. http://example.com/var1:value1/var2:varlue2/
         *
         * @return array of url vars
         */
        public function parameters() {
            return $this->parameter_vars;
        }

        /**
         * Vars assigned during `http` instantiation
         *
         * @param string $key
         *
         * @return string
         */
        public function server($key) {
            if (isset($this->server_vars[$key])) {
                return $this->server_vars[$key];
            } else if (isset($this->server[$key])) {
                return $this->server[$key];
            }
        }

        /**
         * Vars assigned during `http` instantiation
         *
         * @return array
         */
        public function server_vars() {
            return $this->server_vars;
        }

        /**
         * GET var
         *
         * @param string $key
         *
         * @return string|array|null
         */
        public function get($key) {
            if (isset($this->get[$key])) {
                return $this->get[$key];
            }
        }

        /**
         * GET vars
         *
         * @return array
         */
        public function gets() {
            return $this->get;
        }

        /**
         * Checks if a GET var is set
         *
         * @param string $key
         *
         * @return bool
         */
        public function get_isset($key) {
            return isset($this->get[$key]);
        }

        /**
         * POST var
         *
         * @param string $key
         *
         * @return string|array|null
         */
        public function post($key) {
            if (isset($this->post[$key])) {
                return $this->post[$key];
            }
        }

        /**
         * POST vars
         *
         * @return array
         */
        public function posts() {
            return $this->post;
        }

        /**
         * Checks if a POST var is set
         *
         * @param string $key
         *
         * @return bool
         */
        public function post_isset($key) {
            return isset($this->post[$key]);
        }

        /**
         * FILE var
         *
         * @param string $key
         *
         * @return array|null
         */
        public function file($key) {
            if (isset($this->files[$key])) {
                return $this->files[$key];
            }
        }

        /**
         * FILE vars
         *
         * @return array
         */
        public function files() {
            return $this->files;
        }

        /**
         * Raw input from the HTTP request
         *
         * @return string
         */
        public function input() {
            return file_get_contents('php://input');
        }

        /**
         * Cookie value
         *
         * @param string $key
         *
         * @return string|array|null
         */
        public function cookie($key) {
            if (isset($this->cookies[$key])) {
                $this->decode_cookies();
                return $this->cookies[$key];
            }
        }

        /**
         * Cookie values
         *
         * @return array
         */
        public function cookies() {
            $this->decode_cookies();
            return $this->cookies;
        }

        /**
         * Decode cookies (base64)
         */
        protected function decode_cookies() {
            if (! $this->cookies_decoded) {
                foreach ($this->cookies as & $cookie) {
                    $cookie = base64_decode($cookie);
                }
                $this->cookies_decoded = true;
            }
        }

        /**
         * Execute the http request and load the correct controller based on user permissions
         *
         * @throws neoform\redirect\login\exception
         */
        public function execute() {
            // Only run this once
            if ($this->executed) {
                return;
            }

            $this->executed = true;

            $locale = locale::instance();
            $info = route\dao::get($locale->get());
            $locale->set_routes($info['routes']);

            $controllers       = $info['controllers'];
            $controller_class  = null;
            $action_name       = null;
            $controller_secure = false;
            $controller_slugs  = null;

            //
            // Router
            //
            foreach ($this->slugs as $slug) {

                // Add up the slugs as we go along "/first/second/third" etc..
                if ($slug === '/') {
                    $controller = $controllers;
                } else if (isset($controller[$slug])) {
                    $controller = & $controller[$slug];
                } else {
                    break;
                }

                $this->controller_slugs[] = $slug;

                // If permissions set, make sure user has matching permissions
                if ($controller['resource_ids']) {

                    // Authentication
                    if (! isset($auth)) {
                        $auth = auth::instance();
                        $logged_in = $auth->logged_in();

                        if ($logged_in) {
                            $user = $auth->user();
                            if (! $user->is_active()) {
                                throw new neoform\user\status\exception('User account is not active');
                            }
                        }
                    }

                    // If user is logged in
                    if ($logged_in) {
                        // And does not have permission - access denied
                        if (! $user->has_access($controller['resource_ids'])) {
                            if (! $this->config['silent_acccess_denied']) {
                                neoform\http\controller::show403();
                                return;
                            } else {
                                // Skip any remaining controllers, and execute the last valid controller
                                break;
                            }
                        }

                    // If user is not logged in
                    } else {
                        // Ask them to log in
                        throw new neoform\redirect\login\exception($this->server_vars['query'], 'You must be logged in to continue');
                    }
                }

                $controller_class = "\\neoform\\{$controller['controller_class']}";
                $action_name      = $controller['action_name'];
                $controller_slugs = $controller['slugs'];

                if ($controller['secure']) {
                    $controller_secure = true;
                }

                if (! $controller['children']) {
                    break;
                }

                $controller = & $controller['children'];
            }

            // Remove it from the global namespace
            unset($controllers_map);

            //
            // Domain/SSL validation
            //
            $redirect_needed = false;

            // Requested structure is 'secure'
            if ($controller_secure) {
                // Set the url
                $this->server_vars['url'] = $this->server_vars['surl'];

                if ( ($this->config['https']['secure'] && ! $this->server_vars['https']) || (! $this->config['https']['secure'] && $this->server_vars['https']) ) {
                    $redirect_needed = true;
                }

                if ($this->server_vars['subdomain_secure'] != $this->server_vars['subdomain']) {
                    $redirect_needed = true;
                }

            // Requested controller is 'regular'
            } else {
                //set the url
                $this->server_vars['url'] = $this->server_vars['rurl'];

                if ( ($this->config['https']['regular'] && ! $this->server_vars['https']) || (! $this->config['https']['regular'] && $this->server_vars['https']) ) {
                    $redirect_needed = true;
                }

                if ($this->server_vars['subdomain_regular'] != $this->server_vars['subdomain']) {
                    $redirect_needed = true;
                }
            }

            // If the domain name requested does not match the settings, redirect.
            if ($this->config['domain'] != $this->server_vars['domain']) {
                $redirect_needed = true;
            }

            // If a redirect is needed because incorrect protocol or subdomain is being used..
            if ($redirect_needed) {
                output::instance()->redirect($this->server_vars['query']);
                return;
            } else {

                // If it's not in $this->controller_slugs then it goes in $this->non_controller_slugs
                $this->non_controller_slugs[] = array_slice($this->slugs, count($this->controller_slugs));

                // Name the slug variables based on the controller config
                if ($controller_slugs) {
                    foreach ($controller_slugs as $k => $slug_name) {
                        $this->slugs[$slug_name] = isset($this->slugs[$k]) ? $this->slugs[$k] : null;
                        unset($this->slugs[$k]);
                    }
                }

                // if no class set, 404
                if (! $controller_class) {
                    neoform\http\controller::show404();
                } else {
                    if (! $action_name) {
                        $action_name = neoform\http\controller::DEFAULT_ACTION;
                    }

                    $this->server_vars['controller'] = $controller_class;
                    $this->server_vars['action']     = $action_name;

                    $controller = new $controller_class;
                    $controller->$action_name();
                }
            }
        }

        /**
         * Check if the page being accessed was from an internal source and not from a 3rd party website
         * Good for blocking XSRF attacks.
         *
         * @param bool $output_error
         * @param string $rc if is not set, $rc is pulled from GET var
         *
         * @return bool
         * @throws neoform\error\exception
         */
        public function ref($output_error=true, $rc=null) {

            $good = true;

            $cookied_code = $this->cookie(neoform\config::instance()['auth']['cookie']);

            if (! $cookied_code) {
                $cookied_code = neoform\auth\lib::create_hash_cookie();
            }

            $timeout = (int) $this->config['session']['ref_timeout'];
            if ($rc === null) {
                $httphash = isset($this->get['rc']) ? base64_decode($this->get['rc']) : false;
            } else {
                $httphash = base64_decode($rc);
            }
            $timestamp    = substr($httphash, -10);
            $cookiehash   = $this->ref_hash($cookied_code, $timestamp);
            $time         = time();

            // Make sure the code matches the user's cookie
            if (
                ! $httphash
                || ! $cookiehash
                || strcmp($cookiehash, $httphash) !== 0
            ) {
                $good = false;
            }

            try {
                // Make sure the referal domain matches this site
                $referer = trim($this->server('referer'));
                if ($referer) {
                    $url    = parse_url($referer);
                    $domain = isset($url['host']) ? $url['host'] : false;

                    if (! preg_match('`(^' . quotemeta($this->server('domain')) . '$)|(\.' . quotemeta($this->server('domain')) . '$)`i', $domain, $matches)) {
                        $good = false;
                    }
                } else {
                    $good = false;
                }
            } catch (\exception $e) {
                $good = false;
            }

            // Ref code expires if not used for too long..
            if ($good && ($timestamp > $time + $timeout || $timestamp < $time - $timeout)) {
                $good = false;
                if ($output_error) {
                    throw new neoform\error\exception("Your session has timed out, please try again");
                }
            } else if ($output_error && ! $good) {
                throw new neoform\error\exception("There was a problem verifying that your browser was referred here properly.");
            }

            return $good;
        }

        /**
         * Returns URL variable to be used to prevent XSRF attacks
         *
         * @return string
         */
        public function get_ref() {

            if (! $this->ref_code_cache) {

                $cookied_code = $this->cookie(neoform\config::instance()['auth']['cookie']);

                if (! $cookied_code) {
                    $cookied_code = neoform\auth\lib::create_hash_cookie();
                }

                // Append a timestamp so we can have the ref code expire
                $time = time();

                $this->ref_code_cache = rawurlencode(base64_encode($this->ref_hash($cookied_code, $time)));
            }

            return $this->ref_code_cache;
        }

        /**
         * Generates referral hash
         *
         * @param string $code
         * @param integer $timestamp
         *
         * @return string
         */
        protected function ref_hash($code, $timestamp) {

            if (! $this->ref_secret_cache) {
                $this->ref_secret_cache = $this->config['session']['ref_secret'];
            }

            return hash('whirlpool', $code . $timestamp . $this->ref_secret_cache, 1) . $timestamp;
        }

        /**
         * Returns true if this page has been hotlinked, or the name of the domain doing the hotlinking
         *
         * @param bool $return_domain
         *
         * @return bool|string
         */
        public function hotlinked($return_domain=false) {
            static $hotlinked      = null;
            static $refered_domain = null;

            if ($hotlinked === null) {
                $hotlinked = true;
                try {
                    if ($this->server('referer')) {
                        $ref = parse_url(strtolower($this->server('referer')));
                        $refered_domain = join(
                            '.',
                            array_slice(
                                explode('.', isset($ref['host']) ? $ref['host'] : ''),
                                -2
                            )
                        );
                        $hotlinked = $refered_domain !== $this->server('domain');
                    }
                } catch (\exception $e) {
                    //bo
                }
            }

            if ($return_domain) {
                return $refered_domain;
            } else {
                return $hotlinked;
            }
        }
    }