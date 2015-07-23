<?php

    namespace Neoform\Session;

    use Neoform;

    class Xsrf {

        const SESSION_TOKEN_KEY = 'rc';

        /**
         * @var Neoform\Request\Model
         */
        protected $request;

        /**
         * @var Neoform\Session\Config
         */
        protected $config;

        /**
         * @var string
         */
        protected $sessionToken;

        /**
         * @param Neoform\Request\Model $request
         * @param Neoform\Session\Config $config
         */
        public function __construct(Neoform\Request\Model $request, Neoform\Session\Config $config) {
            $this->request = $request;
            $this->config  = $config;
        }

        /**
         * @throws Neoform\Error\Exception
         */
        public function isRequestValid() {

            if (! $this->request->getGet()->exists(self::SESSION_TOKEN_KEY)) {
                return false;
            }

            if (! $this->request->getServer()->isReferredInternally()) {
                return false;
            }

            return $this->isTokenValid($this->request->getGet()->get(self::SESSION_TOKEN_KEY));
        }

        /**
         * Check if the page being accessed was from an internal source and not from a 3rd party website
         * Good for blocking XSRF attacks.
         *
         * @param string $token
         *
         * @return bool
         */
        public function isTokenValid($token) {

            $token = base64_decode($token);

            if (! $token) {
                return false;
            }

            // Extract the timestamp off the end of the session token
            $tokenCreatedOn = substr($token, -10);

            // Check if the timestamp is within the timeout
            $now     = time();
            $timeout = (int) $this->config->getXsrfTtl();

            // Must be within the window
            if ($tokenCreatedOn > $now + $timeout || $tokenCreatedOn < $now - $timeout) {
                // Timed out
                return false;
            }

            // Generate a valid session token based on cookie session secret and timestamp
            $validToken = $this->generateToken(
                $this->request->getSession()->getToken(),
                $tokenCreatedOn
            );

            // Make sure the session token matches the valid cookie session token
            if (strcmp($validToken, $token) !== 0) {
                return false;
            }

            return true;
        }

        /**
         * Get token to be passed via GET/POST (or something similar, HTTP header maybe?)
         *
         * @return string
         */
        public function getToken() {
            return base64_encode($this->generateToken(
                $this->request->getSession()->getToken()
            ));
        }

        /**
         * Get the token key (used in GET)
         *
         * @return string
         */
        public function getTokenKey() {
            return self::SESSION_TOKEN_KEY;
        }

        /**
         * Generates session token
         *
         * @param string  $secret
         * @param integer $unixTimestamp
         *
         * @return string
         */
        protected function generateToken($secret, $unixTimestamp=null) {
            if ($unixTimestamp === null) {
                $unixTimestamp = time();
            }
            return hash('whirlpool', $secret . $unixTimestamp . $this->config->getXsrfSalt(), 1) . $unixTimestamp;
        }
    }