<?php

    namespace neoform\render;

    use neoform\http;
    use neoform\output;

    class json {

        protected $vars;

        public function __construct(array $preload_vars=[]) {
            $this->vars = $preload_vars;
            $this->vars['_ref'] = http::instance()->get_ref();
        }

        public function execute() {
            output::instance()->output_type('json')->send_headers();
            echo json_encode($this->vars);
        }

        public function render() {
            output::instance()->output_type('json')->body(json_encode($this->vars));
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

