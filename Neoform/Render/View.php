<?php

    namespace Neoform\Render;

    use Neoform;

    interface View {

        /**
         * @return string
         */
        public function render();

        /**
         * @param Neoform\Request\Model $request
         *
         * @return $this
         */
        public function setRequest(Neoform\Request\Model $request);
    }