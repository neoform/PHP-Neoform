<?php

    class web_browser_model {

        protected $info;

        public function __construct($agent_string=null) {
            if ($agent_string === null) {
                $agent_string = core::http()->server('agent');
            }

            try {
                $this->info = @get_browser($agent_string, true);
            } catch (exception $e) {
                $this->info = [];
            }
        }

        public function __get($k) {
            if (isset($this->info[$k])) {
                return $this->info[$k];
            }
        }

        public function is_crawler() {
            return (bool) $this->crawler;
        }

        /*
            [browser_name_regex] => ^mozilla/5\.0 (windows; .; windows nt 5\.1; .*rv:.*) gecko/.* firefox/0\.9.*$
            [browser_name_pattern] => Mozilla/5.0 (Windows; ?; Windows NT 5.1; *rv:*) Gecko/* Firefox/0.9*
            [parent] => Firefox 0.9
            [platform] => WinXP
            [browser] => Firefox
            [version] => 0.9
            [majorver] => 0
            [minorver] => 9
            [cssversion] => 2
            [frames] => 1
            [iframes] => 1
            [tables] => 1
            [cookies] => 1
            [backgroundsounds] =>
            [vbscript] =>
            [javascript] => 1
            [javaapplets] => 1
            [activexcontrols] =>
            [cdf] =>
            [aol] =>
            [beta] => 1
            [win16] =>
            [crawler] =>
            [stripper] =>
            [wap] =>
            [netclr] =>
        */
    }