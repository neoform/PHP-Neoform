<?php

    namespace Neoform\Render;

    use Neoform;

    class Dialog extends Neoform\Render\Json {

        /**
         * @var array
         */
        protected $parameters;

        /**
         * @var string
         */
        protected $path;

        /**
         * @param string $path
         * @param array|null $parameters
         */
        public function __construct($path, array $parameters=null) {
            $this->path       = $path;
            $this->parameters = $parameters ?: [];
        }

        /**
         * @param Neoform\Request\Model $request
         *
         * @return $this
         */
        public function setRequest(Neoform\Request\Model $request) {
            parent::setRequest($request);
            $this->parameters['request'] = $request;
            return $this;
        }

        /**
         * Set a parameter/variable to be used by the header/body/footer or callback
         *
         * @param string $k
         * @param string $v
         *
         * @return $this
         */
        public function setParam($k, $v) {
            $this->parameters[$k] = $v;
            return $this;
        }

        /**
         * Apply custom CSS to the dialog box
         *
         * @param string      $k
         * @param string|null $v
         *
         * @return $this
         */
        public function setCss($k, $v=null) {
            if (! isset($this->vars['css'])) {
                $this->vars['css'] = [];
            }

            if (is_array($k)) {
                $this->vars['css'] += $k;
            } else {
                $this->vars['css'][$k] = $v;
            }

            return $this;
        }

        /**
         * Set the title of the dialog box
         *
         * @param string $v
         *
         * @return $this
         */
        public function setTitle($v) {
            $this->vars['content']['title'] = $v;
            return $this;
        }

        /**
         * Assign content to a given part of the dialog box
         *
         * @param string $name (eg, title, body, foot)
         *
         * @return $this
         */
        public function setContent($name) {
            $this->vars['content'][$name] = (string) (new Dialog\View("{$this->path}/{$name}", $this->parameters))->render();
            return $this;
        }

        /**
         * Run JS code callbacks in the dialog
         *
         * @param string $name callback event name
         *
         * @return $this
         */
        public function setCallback($name) {

            $callback = trim((string) (new Dialog\View("{$this->path}/{$name}", $this->parameters))->render());

            // Remove <script></script> from callback template string
            if (substr($callback, 0, 8) === '<script>') {
                $this->vars['callbacks'][$name] = substr($callback, 8, -9);
            } else {
                $this->vars['callbacks'][$name] = $callback;
            }

            return $this;
        }
    }

