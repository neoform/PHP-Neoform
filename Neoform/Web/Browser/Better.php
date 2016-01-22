<?php

    namespace Neoform\Web\Browser;

    class Better {

        /**
         * @var string
         */
        protected $agentString;

        /**
         * @var bool
         */
        protected $isCrawler = false;

        /**
         * @var bool
         */
        protected $isMobile = false;

        /**
         * @var bool
         */
        protected $isTablet = false;

        /**
         * @var string
         */
        protected $browserName;

        /**
         * @param string $agentString
         */
        public function __construct($agentString) {

            if (! $agentString) {
                return;
            }

            $this->agentString = $agentString;

            $this->parseCrawler();
            $this->parsePlatform();
            $this->parseBrowser();
        }

        /**
         * @return string
         */
        public function getBrowserName() {
            return $this->browserName;
        }

        /**
         * @return bool
         */
        public function isCrawler() {
            return (bool) $this->isCrawler;
        }

        /**
         * @return bool
         */
        public function isMobile() {
            return $this->isMobile;
        }

        /**
         * @return bool
         */
        public function isTablet() {
            return $this->isMobile;
        }

        /**
         * @return bool
         */
        public function isPhone() {
            return $this->isMobile && ! $this->isTablet;
        }

        /**
         * Determine if the agent is a crawler
         */
        protected function parseCrawler() {
            if (preg_match('`(robot|bot|slurp|search|spider)`i', strtolower($this->agentString))) {
                $this->isCrawler = true;
            }
        }

        /**
         * Determine if the agent is mobile or tablet
         */
        protected function parsePlatform() {

            $userAgent = strtolower($this->agentString);

            // Tablet user agent (including Android -tablet devices- VS Andoid Mobile -phone devices-
            if (preg_match('`(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))`i', $userAgent)) {
                $this->isTablet = true;
            }

            // Mobile user agent
            if (preg_match('`(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)`i', $userAgent)) {
                $this->isMobile = true;
            }

            // More mobile user agent
            $mobileAgents = [
                'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
                'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
                'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
                'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
                'newt','noki','palm','pana','pant','phil','play','port','prox',
                'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
                'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
                'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
                'wapr','webc','winw','winw','xda ','xda-',
            ];

            if (in_array(strtolower(substr($this->agentString, 0, 4)), $mobileAgents)) {
                $this->isMobile = true;
            }

            // Mobile user agent, 1st version
            $fragment = [
                'iphone', 'ipod' , 'blackberry' , 'htc' , 'palm' , 'Samsung' , 'Nokia' , 'SGH' , 'SCH' ,
                'LG' , 'Android' , 'MOT' , 'Motorolla' , 'SonyEricsson' , 'Sony' , 'PSP' , 'Kyocera' , 'ASUS' ,
                'PANTECH' , 'Audiovox' , 'BenQ' , 'Fly' , 'i-mobile' , 'LENOVO' , 'Haier' , 'i-mate' , 'Hyundai' ,
                'Nintendo DSi' , 'O2 Xda' , 'Sagem' , ' SIE ' , 'Vario' , 'winwap' , 'DoCoMo' , 'NetFront' ,
                'Vodafone' , 'UP.Browser' , 'konka' , 'tianyu' , 'ktouch' , 'series60' , 'kddi' , 'sagem' , 'MIDP' ,
                'CLDC' , 'SoftBank' , 'TelecaBrowser' , 'Teleca' , 'Symbian' , 'Treo' , 'WAP' , 'pocket' , 'kindle' ,
                'mobile' , 'Opera Mini' , 'Obigo' , 'Windows Mobile' , 'Windows CE' , 'OPWV' , 'T-Mobile' , 'webOS',
            ];

            if (preg_match('`(' . implode('|', $fragment) . ')`i', $userAgent, $matches)) {
                $this->isMobile = true;
            }
        }

        /**
         * Determine the browser name
         */
        protected function parseBrowser() {
            // If it's a crawler, look for different strings
            if ($this->isCrawler) {
                $browsers = [
                    'google' => 'Google Bot',
                    'bing'   => 'Bing Bot',
                    'yahoo'  => 'Yahoo! Bot',
                    'yandex' => 'Yandex Bot',
                    'baidu'  => 'Baidu Bot',
                ];

                $userAgent = strtolower($this->agentString);

                foreach ($browsers as $search => $browserName) {
                    if (strstr($userAgent, $search) !== false) {
                        $this->browserName = $browserName;
                        return;
                    }
                }

                // Try to match something
                if (preg_match('`([a-z0-9\-]+bot)`i', $userAgent, $match)) {
                    $this->browserName = $match[1];
                    return;
                }

            // Not a crawler
            } else {
                $browsers = [
                    'firefox' => 'Firefox',
                    'msie'    => 'Internet Explorer',
                    'chrome'  => 'Chrome',
                    'safari'  => 'Safari',
                ];

                $userAgent = strtolower($this->agentString);

                foreach ($browsers as $search => $browserName) {
                    if (strstr($userAgent, $search) !== false) {
                        $this->browserName = $browserName;
                        return;
                    }
                }
            }

            $this->browserName = 'Other/Unknown';
        }
    }
