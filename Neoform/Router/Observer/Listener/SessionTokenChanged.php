<?php

    namespace Neoform\Router\Observer\Listener;

    use Neoform;

    class SessionTokenChanged implements Neoform\Observer\Listener {

        public function update(Neoform\Router\Observer\Event\BeforeRouting $event) {

            $request = $event->getRequest();

            if ($request->getSession()->hasTokenChanged()) {
                $event->getResponse()->setCookie(
                    $request->getSession()->getSessionCookieKey(),
                    $request->getSession()->getToken()
                );
            }
        }
    }