<?php

    namespace Neoform\Session;

    use DateInterval;
    use DateTime;
    use Neoform;

    class Auth {

        /**
         * @var string
         */
        protected $sessionToken;

        /**
         * @var Neoform\Auth\Config
         */
        protected $config;

        /**
         * @var Auth
         */
        protected $auth;

        /**
         * @param Model               $session
         * @param Neoform\Auth\Config $config
         */
        public function __construct(Neoform\Session\Model $session, Neoform\Auth\Config $config) {
            $this->sessionToken = $session->getToken();
            $this->config       = $config;

            try {
                if ($this->auth = Neoform\Auth\Model::fromPk($this->sessionToken)) {
                    if ((new DateTime) < $this->auth->expires_on) {
                        return;
                    }

                    // Expired session - delete
                    Neoform\Auth\Dao::get()->delete($this->auth);
                }

            } catch (Neoform\Auth\Exception $e) {

            }

            // Create an auth session with the current token
            $this->auth = Neoform\Auth\Model::fromArray([
                'hash' => $this->sessionToken,
            ]);
        }

        /**
         * Create an auth record to track who is logged in with this session
         *
         * @param Neoform\User\Model $user
         * @param bool               $remember
         *
         * @return Neoform\Auth\Model
         */
        public function setUser(Neoform\User\Model $user, $remember=false) {

            // Create expiry date for session
            $expires = new DateTime;
            if ($remember) {
                $expires->add(new DateInterval("P{$this->config->getLongAuthLifetime()}"));
            } else {
                $expires->add(new DateInterval("P{$this->config->getNormalAuthLifetime()}"));
            }

            // Create session
            $auth = Neoform\Auth\Dao::get()->insert([
                'user_id'    => $user->id,
                'expires_on' => $expires->format('Y-m-d H:i:s'),
                'hash'       => $this->sessionToken,
            ]);

            // Update last login
            Neoform\User\Date\Dao::get()->update(
                $user->user_date(),
                [
                    'last_login' => (new Neoform\Type\Date)->getTimeStamp(),
                ]
            );

            return $auth;
        }

        /**
         * Log the user out by destroying their auth session/record
         */
        public function destroy() {
            // Expired session - delete
            Neoform\Auth\Dao::get()->delete($this->auth);
        }

        /**
         * @return Neoform\User\Model|null
         */
        public function getUser() {
            return $this->auth->getUser();
        }

        /**
         * @return integer|null
         */
        public function getUserId() {
            return $this->auth->user_id;
        }

        /**
         * @return bool
         */
        public function isLoggedIn() {
            return $this->auth->loggedIn();
        }
    }