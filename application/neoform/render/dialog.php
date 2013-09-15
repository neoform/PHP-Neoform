<?php

    namespace neoform\render;

    use neoform;

    class dialog {

        protected $json_data  = [];
        protected $parameters;
        protected $path;

        public function __construct($path, $parameters=null) {
            $this->path = $path;

            if (is_array($parameters)) {
                $this->parameters = $parameters;
            } else {
                $this->parameters = [];
            }

            $this->json_data['_ref'] = neoform\http::instance()->get_ref();
        }

        /**
         * Render dialog and send output to buffer
         */
        public function render() {
            neoform\output::instance()->output_type('json')->body(json_encode($this->json_data));
        }

        /**
         * Save render to string
         *
         * @return string
         */
        public function __tostring() {
            return (string) $this->execute();
        }

        /**
         * Set a parameter/variable to be used by the header/body/footer or callback
         *
         * @param string $k
         * @param string $v
         */
        public function __set($k, $v) {
            $this->parameters[$k] = $v;
        }

        /**
         * Set a parameter/variable to be used by the header/body/footer or callback
         *
         * @param string $k
         * @param string $v
         *
         * @return dialog
         */
        public function set_param($k, $v) {
            $this->parameters[$k] = $v;
            return $this;
        }

        /**
         * Apply custom CSS to the dialog box
         *
         * @param string      $k
         * @param string|null $v
         *
         * @return dialog
         */
        public function css($k, $v=null) {
            if (! isset($this->json_data['css'])) {
                $this->json_data['css'] = [];
            }

            if (is_array($k)) {
                $this->json_data['css'] += $k;
            } else {
                $this->json_data['css'][$k] = $v;
            }
            return $this;
        }

        /**
         * Set the title of the dialog box
         *
         * @param string $v
         *
         * @return dialog
         */
        public function title($v) {
            $this->json_data['content']['title'] = $v;
            return $this;
        }

        /**
         * Assign content to a given part of the dialog box
         *
         * @param string $name (eg, title, body, foot)
         *
         * @return dialog
         */
        public function content($name) {
            $this->json_data['content'][$name] = (string) (new dialog\view("{$this->path}/{$name}", $this->parameters));
            return $this;
        }

        /**
         * Run JS code callbacks in the dialog
         *
         * @param string $name callback event name
         *
         * @return dialog
         */
        public function callback($name) {

            $callback = trim((string) (new dialog\view("{$this->path}/{$name}", $this->parameters)));

            // Remove <script></script> from callback template string
            if (substr($callback, 0, 8) === '<script>') {
                $this->json_data['callbacks'][$name] = substr($callback, 8, -9);
            } else {
                $this->json_data['callbacks'][$name] = $callback;
            }

            return $this;
        }
    }

