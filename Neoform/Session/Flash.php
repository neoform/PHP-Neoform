<?php

    namespace Neoform\Session;

    use Neoform;

    /**
     * Short term data storage - useful for very short lived sessions, data is highly volatile and not guarantied to
     * exist on read. Best uses are for redirect session data.
     */
    class Flash {

        /**
         * @var Model
         */
        protected $session;

        /**
         * @var Config
         */
        protected $config;

        /**
         * @param Model                  $session
         * @param Neoform\Session\Config $config
         */
        public function __construct(Neoform\Session\Model $session, Neoform\Session\Config $config) {
            $this->session = $session;
            $this->config  = $config;
        }

        /**
         * @return Neoform\Service\Service
         */
        protected function readEngine() {
            $engine = $this->config->getFlashCacheEngine();
            return $engine::getService($this->config->getFlashCachePoolRead());
        }

        /**
         * @return Neoform\Service\Service
         */
        protected function writeEngine() {
            $engine = $this->config->getFlashCacheEngine();
            return $engine::getService($this->config->getFlashCachePoolWrite());
        }

        /**
         * Get a flash value by key
         *
         * @param string $key
         *
         * @return mixed
         */
        public function get($key) {
            $hash = base64_encode($this->session->getToken());
            return $this->readEngine()->get()->get("http_flash:{$hash}:{$key}");
        }

        /**
         * Set a value in flash
         *
         * @param string       $key
         * @param string       $val
         * @param integer|null $ttl
         *
         * @return mixed
         */
        public function set($key, $val, $ttl=null) {
            $hash = base64_encode($this->session->getToken());
            return $this->writeEngine()->get()->set(
                "http_flash:{$hash}:{$key}",
                $val,
                $ttl !== null ? $ttl : (int) $this->config->getDefaultFlashTtl()
            );
        }

        /**
         * Delete a value from flash
         *
         * @param string $key
         *
         * @return mixed
         */
        public function del($key) {
            $hash = base64_encode($this->session->getToken());
            return $this->writeEngine()->get()->delete("http_flash:{$hash}:{$key}");
        }
    }