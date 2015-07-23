<?php

    namespace Neoform\Render;

    use Neoform;

    /**
     * Class Buffer
     *
     * A view containing data built with a buffer
     *
     * @package Neoform\Render
     */
    class Buffer implements View {

        /**
         * @var string
         */
        protected $lines = [];

        /**
         * @return string
         */
        public function render() {
            return join($this->lines);
        }

        /**
         * @param string $line
         */
        public function append($line) {
            $this->lines[] = $line;
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

