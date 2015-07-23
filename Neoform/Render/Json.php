<?php

    namespace Neoform\Render;

    use Neoform;

    class Json implements View {

        /**
         * @var array
         */
        protected $vars;

        /**
         * @param Neoform\Request\Model $request
         * @return $this
         */
        public function setRequest(Neoform\Request\Model $request) {
            $this->vars['_xsrf'] = $request->getSession()->getXsrf()->getToken();
            return $this;
        }

        /**
         * @return string
         */
        public function __toString() {
            return (string) json_encode($this->vars);
        }

        /**
         * @return string
         */
        public function render() {
            return (string) json_encode($this->vars);
        }

        /**
         * @param string $k
         * @param mixed  $v
         */
        public function __set($k, $v) {
            $this->vars[$k] = $v;
        }

        /**
         * @param string $k
         * @param mixed  $v
         *
         * @return $this
         */
        public function set($k, $v) {
            $this->vars[$k] = $v;
            return $this;
        }
    }

