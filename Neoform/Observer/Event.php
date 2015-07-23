<?php

    namespace Neoform\Observer;

    abstract class Event {

        /**
         * @var Listener[]
         */
        private $listeners = [];

        /**
         *
         */
        public function __construct() {

        }

        /**
         * Attach a listener to an event
         *
         * @param Listener $listener
         *
         * @return $this
         */
        final public function attach(Listener $listener) {
            $this->listeners[] = $listener;
            return $this;
        }

        /**
         * Detach Listener from event
         *
         * @param Listener $listener
         *
         * @return $this
         */
        final public function detach(Listener $listener) {
            if ($key = array_search($listener, $this->listeners, true)) {
                unset($this->listeners[$key]);
            }
            return $this;
        }

        /**
         * Notify all listeners
         *
         * @return $this
         */
        final public function notify() {
            foreach ($this->listeners as $value) {
                $value->update($this);
            }
            return $this;
        }
    }