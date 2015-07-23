<?php

    namespace Neoform\Assets\Processor;

    use Neoform;

    class Css extends Neoform\Assets\Processor {

        /**
         * @var Neoform\Assets\Processor\Css\Config
         */
        protected $patternConfig;

        /**
         * Init
         */
        protected function init() {
            $this->patternConfig = Neoform\Assets\Processor\Css\Config::get();
        }

        /**
         * Compile
         */
        public function compile() {

            // Search/Replace
            if ($this->patternConfig->getSearchReplace()) {
                foreach ($this->patternConfig->getSearchReplace() as $search => $replace) {
                    $this->content = str_replace($search, $replace, $this->content);
                }
            }

            // Regex
            if ($this->patternConfig->getPatterns()) {
                foreach ($this->patternConfig->getPatterns() as $search => $replace) {
                    if (is_callable($replace)) {
                        $this->content = preg_replace_callback($search, $replace, $this->content);
                    } else {
                        $this->content = preg_replace($search, $replace, $this->content);
                    }
                }
            }
        }
    }