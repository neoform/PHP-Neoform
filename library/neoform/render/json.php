<?php

    namespace neoform;

    class render_json {

        protected $vars;

        public function __construct($preload_vars=false) {
            if (\is_array($preload_vars)) {
                $this->vars = $preload_vars;
            } else {
                $this->vars = [];
            }

            $this->vars['_ref'] = core::http()->get_ref();
        }

        public function execute() {
            core::output()->output_type('json')->send_headers();
            echo json_encode($this->vars);
        }

        public function render() {
            core::output()->output_type('json')->body(\json_encode($this->vars));
        }

        public function __get($k) {
            if (isset($this->__vars[$k])) {
                return $this->__vars[$k];
            }
        }

        public function __set($k, $v) {
            $this->vars[$k] = $v;
        }

        public function __tostring() {
            return (string) $this->execute();
        }
    }

