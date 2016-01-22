<?php

    namespace Neoform\Web\Browser;

    class Model {

        /**
         * @var string[]
         */
        protected static $cache = [];

        /**
         * @var array
         */
        protected $info;

        /**
         * @param string $agentString
         */
        public function __construct($agentString) {
            if (isset(self::$cache[$agentString])) {
                $this->info = self::$cache[$agentString];
                return;
            }

            if (! $agentString) {
                $this->info = [];
                return;
            }

            try {
                $this->info = @get_browser($agentString, true);
            } catch (\Exception $e) {
                $this->info = [];
            }

            self::$cache[$agentString] = $this->info;
        }

        /**
         * [
         *     [browser_name_regex] => ^mozilla/5\.0 (windows; .; windows nt 5\.1; .*rv:.*) gecko/.* firefox/0\.9.*$
         *     [browser_name_pattern] => Mozilla/5.0 (Windows; ?; Windows NT 5.1; *rv:*) Gecko/* Firefox/0.9*
         *     [parent] => Firefox 0.9
         *     [platform] => WinXP
         *     [browser] => Firefox
         *     [version] => 0.9
         *     [majorver] => 0
         *     [minorver] => 9
         *     [cssversion] => 2
         *     [frames] => 1
         *     [iframes] => 1
         *     [tables] => 1
         *     [cookies] => 1
         *     [backgroundsounds] =>
         *     [vbscript] =>
         *     [javascript] => 1
         *     [javaapplets] => 1
         *     [activexcontrols] =>
         *     [cdf] =>
         *     [aol] =>
         *     [beta] => 1
         *     [win16] =>
         *     [crawler] =>
         *     [stripper] =>
         *     [wap] =>
         *     [netclr] =>
         * ];
         *
         * @param string $k
         *
         * @return mixed|null
         */
        public function __get($k) {
            if (isset($this->info[$k])) {
                return $this->info[$k];
            }
        }

        /**
         * @return bool
         */
        public function isCrawler() {
            return (bool) $this->crawler;
        }

        /**
         * @return string|null
         */
        public function getBrowser() {
            return (string) $this->browser ?: null;
        }

        /**
         * @return string|null
         */
        public function getBrowserVersion() {
            return (string) $this->version ?: null;
        }

        /**
         * @return string|null
         */
        public function getOS() {
            return (string) $this->platform ?: null;
        }
    }