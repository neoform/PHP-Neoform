<?php

    namespace Neoform\Render;

    use Neoform;

    /**
     * Class Raw
     *
     * A view containing a blob of data (needs to be string or binary)
     *
     * @package Neoform\Render
     */
    class Raw implements View {

        /**
         * @var string
         */
        protected $val;

        /**
         * @param string $val
         */
        public function __construct($val) {
            $this->val = $val;
        }

        /**
         * @return string
         */
        public function render() {
            return $this->val;
        }

        /**
         * @param Neoform\Request\Model $request
         *
         * @return $this
         */
        public function setRequest(Neoform\Request\Model $request) {
            return $this;
        }
    }

