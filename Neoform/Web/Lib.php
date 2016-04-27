<?php

    namespace Neoform\Web;

    use Neoform;

    class Lib {

        /**
         * @var resource Curl handle - for reuse between connections (to avoid exhausting the PHP resource pool)
         */
        private static $curl;

        /**
         * @return resource
         */
        private static function getCurl() {
            if (! self::$curl) {
                self::$curl = curl_init();
            }

            return self::$curl;
        }

        /**
         * Get the contents of a URL
         *
         * @param string       $url
         * @param array|string $post
         * @param string|null  $bindToIp
         * @param array|null   $cookies
         * @param string|null  $userAgent
         *
         * @return string
         * @throws exception
         */
        public static function wget($url, $post=null, $bindToIp=null, array $cookies=null, $userAgent=null) {

            if (! self::validUrl($url)) {
                throw new Exception('Invalid URL');
            }

            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_USERAGENT, $userAgent ?: Neoform\Web\Config::get()->getUserAgent());
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);

            // Weird glitch causes gzipped requests to ncix.com to not be decoded if this is not set (even though it's empty)
            curl_setopt($curl, CURLOPT_ENCODING , '');

            if ($bindToIp) {
                curl_setopt($curl, CURLOPT_INTERFACE, $bindToIp);
            }

            if ($post !== null) {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
            }

            if ($cookies) {
                $arr = [];
                foreach ($cookies as $k => $v) {
                    $arr[] = rawurlencode($k) . '=' . rawurlencode($v);
                }
                curl_setopt($curl, CURLOPT_COOKIE, join('; ', $arr));
            }

            $contents = curl_exec($curl);
            $info     = curl_getinfo($curl);

            curl_close($curl);

            if ($info['http_code'] === 200) {
                return $contents;
            }

            throw new Exception("Server returned HTTP/{$info['http_code']}" . ($contents ? " - {$contents}" : ''), (int) $info['http_code']);
        }

        /**
         * Get the contents of a URL
         *
         * @param string       $url
         * @param array|string $post
         * @param string|null  $bindToIp
         * @param array|null   $cookies
         * @param string|null  $userAgent
         *
         * @return string
         * @throws exception
         */
        public static function get($url, $post=null, $bindToIp=null, array $cookies=null, $userAgent=null) {

            if (! self::validUrl($url)) {
                throw new Exception('Invalid URL');
            }

            $curl = self::getCurl();

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_USERAGENT, $userAgent ?: Neoform\Web\Config::get()->getUserAgent());
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);

            // Weird glitch causes gzipped requests to ncix.com to not be decoded if this is not set (even though it's empty)
            curl_setopt($curl, CURLOPT_ENCODING , '');

            if ($bindToIp) {
                curl_setopt($curl, CURLOPT_INTERFACE, $bindToIp);
            }

            // Post
            curl_setopt($curl, CURLOPT_POST, $post !== null);
            if ($post) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
            }

            // Cookies
            $arr = [];
            if ($cookies) {
                foreach ($cookies as $k => $v) {
                    $arr[] = rawurlencode($k) . '=' . rawurlencode($v);
                }
            }
            curl_setopt($curl, CURLOPT_COOKIE, join('; ', $arr) ?: null);

            $contents = curl_exec($curl);
            $info     = curl_getinfo($curl);

            if ($info['http_code'] === 200) {
                return $contents;
            }

            throw new Exception("Server returned HTTP/{$info['http_code']}" . ($contents ? " - {$contents}" : ''), (int) $info['http_code']);
        }

        /**
         * Get just the header of a URL
         *
         * @param string      $url
         * @param array       $post
         * @param string|null $bind_to_ip
         *
         * @return array
         * @throws exception
         */
        public static function wget_info($url, array $post=null, $bind_to_ip=null) {

            if (! self::validUrl($url)) {
                throw new Exception('Invalid URL');
            }

            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_USERAGENT, Neoform\Web\Config::get()->getUserAgent());
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);

            curl_setopt($curl, CURLOPT_HEADER, 1); // get the header
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);

            // Weird glitch causes gzipped requests to ncix.com to not be decoded if this is not set (even though it's empty)
            curl_setopt($curl, CURLOPT_ENCODING , '');

            if ($bind_to_ip) {
                curl_setopt($curl, CURLOPT_INTERFACE, $bind_to_ip);
            }

            if ($post) {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
            }

            curl_exec($curl);
            $info = curl_getinfo($curl);

            curl_close($curl);

            if ($info['http_code'] === 200) {
                return $info;
            }

            throw new Exception("Server returned HTTP/{$info['http_code']}", (int) $info['http_code']);
        }

        /**
         * Get the header and body of a URL
         *
         * @param string      $url
         * @param array       $post
         * @param string|null $bind_to_ip
         *
         * @return array
         * @throws exception
         */
        public static function wget_full($url, array $post=null, $bind_to_ip=null) {

            if (! self::validUrl($url)) {
                throw new Exception('Invalid URL');
            }

            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_USERAGENT, Neoform\Web\Config::get()->getUserAgent());
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);

            if ($bind_to_ip) {
                curl_setopt($curl, CURLOPT_INTERFACE, $bind_to_ip);
            }

            if ($post) {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
            }

            $body = curl_exec($curl);
            $head = curl_getinfo($curl);

            if ($head['http_code'] === 200) {
                return [
                    'head' => $head,
                    'body' => $body,
                ];
            }

            throw new Exception("Server returned HTTP/{$head['http_code']}", (int) $head['http_code']);
        }

        /**
         * Tests if a URL is valid to be fetched or not
         *
         * @param string $url
         *
         * @return bool
         */
        public static function validUrl($url) {
            if (! $info = parse_url($url)) {
                return false;
            }

            if (empty($info['host']) || $info['host'] === 'localhost') {
                return false;
            }

            if (empty($info['scheme']) || ! ($info['scheme'] === 'http' || $info['scheme'] === 'https')) {
                return false;
            }

            return true;
        }

        /**
         * Checks a robots.txt file if a given URL is crawlable by a given user agent
         *
         * @param string $robots_txt
         * @param string $url
         * @param string $agent_string
         *
         * @return bool
         */
        function robots_allowed($robots_txt, $url, $agent_string) {
            $agents = join('|', [
                preg_quote('*'),
                preg_quote($agent_string),
            ]);

            // if there isn't a robots, then we're allowed in
            if (! trim($robots_txt)) {
                return true;
            }

            $rules       = [];
            $ruleApplies = false;

            foreach (explode("\n", $robots_txt) as $line) {

                $line = trim($line);

                // skip blank lines
                if (! $line) {
                    continue;
                }

                // following rules only apply if User-agent matches $useragent or '*'
                if (preg_match('`^\s*User-agent:\s*(.*)$`i', $line, $match)) {
                    $ruleApplies = (bool) preg_match("`({$agents})`i", $match[1]);
                }

                if ($ruleApplies && preg_match('`^\s*Disallow:\s*(.*)$`i', $line, $regs)) {
                    // an empty rule implies full access - no further tests required
                    if (! $regs[1]) {
                        return true;
                    }

                    // add rules that apply to array for testing
                    $rules[] = preg_quote(trim($regs[1]), '/');
                }
            }

            // parse url to retrieve host and path
            $parsed = parse_url($url);

            foreach ($rules as $rule) {
                // check if page is disallowed to us
                if (preg_match("/^{$rule}/", $parsed['path'])) {
                    return false;
                }
            }

            // page access is allowed
            return true;
        }

        /**
         * The reverse of parse_url() - doesn't handle port numbers.. cause... meh who uses port numbers these days?
         * 
         * @param array $url
         *
         * @return string
         */
        public static function unparseUrl(array $url) {
            return "{$url['scheme']}://{$url['host']}" . (isset($url['path']) ? $url['path'] : '') . (isset($url['query']) ? "?{$url['query']}" : '');
        }

        /**
         * Take a URL and add a GET query parameter. If the parameter already exists, it is overwritten/replaced.
         * 
         * @param string $url
         * @param array  $queryValues
         *
         * @return string
         */
        public static function addQueryValue($url, array $queryValues) {
            $parsed = parse_url($url);

            if (!empty($parsed['query'])) {
                parse_str($parsed['query'], $query);
                foreach ($queryValues as $k => $v) {
                    $query[$k] = $v;
                }
            } else {
                $query = $queryValues;
            }

            $parsed['query'] = http_build_query($query);

            return self::unparseUrl($parsed);
        }
    }
