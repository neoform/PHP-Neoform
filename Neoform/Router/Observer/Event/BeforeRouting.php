<?php

    namespace Neoform\Router\Observer\Event;

    use Neoform;

    class BeforeRouting extends Neoform\Observer\Event {

        /**
         * @var Neoform\Request\Model
         */
        protected $request;
        /**
         * @var Neoform\Response\Builder
         */
        protected $response;

        /**
         * @param Neoform\Request\Model $request
         *
         * @return $this
         */
        public function setRequest(Neoform\Request\Model $request) {
            $this->request = $request;
            return $this;
        }

        /**
         * @return Neoform\Request\Model
         */
        public function getRequest() {
            return $this->request;
        }

        /**
         * @return Neoform\Response\Builder
         */
        public function getResponse() {
            return $this->response;
        }

        /**
         * @param Neoform\Response\Builder $response
         *
         * @return $this
         */
        public function setResponse(Neoform\Response\Builder $response) {
            $this->response = $response;
            return $this;
        }
    }