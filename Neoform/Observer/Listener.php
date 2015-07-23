<?php

    namespace Neoform\Observer;

    use Neoform;

    interface Listener {

        /**
         * @param Event $event
         */
        public function update(Event $event);
    }