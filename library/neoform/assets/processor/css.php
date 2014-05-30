<?php

    namespace neoform\assets\processor;

    use neoform;

    class css extends neoform\assets\processor {

        protected $pattern_config;

        protected function init() {
            $this->pattern_config = neoform\config::instance()['assets\processor\css'];
        }

        public function compile() {

            // Search/Replace
            if ($this->pattern_config['search_replace']) {
                foreach ($this->pattern_config['search_replace'] as $search => $replace) {
                    $this->content = str_replace($search, $replace, $this->content);
                }
            }

            // Regex
            if ($this->pattern_config['patterns']) {
                foreach ($this->pattern_config['patterns'] as $search => $replace) {
                    if (is_callable($replace)) {
                        $this->content = preg_replace_callback($search, $replace, $this->content);
                    } else {
                        $this->content = preg_replace($search, $replace, $this->content);
                    }
                }
            }
        }
    }