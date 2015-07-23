<?php

    namespace Neoform\Redis;

    use Redis as PhpRedis;

    class Redis extends PhpRedis {

        /**
         * @var bool
         */
        protected $isBatchActive = false;

        /**
         * @return $this
         * @throws Exception
         */
        public function multi($name=null) {
            if ($this->isBatchActive) {
                throw new Exception('Pipeline/batch operation already in progress');
            }

            $this->isBatchActive = true;
            return $name ? parent::multi($name) : parent::multi();
        }

        /**
         * @param string|int|null $name
         *
         * @return array
         * @throws Exception
         */
        public function exec($name=null) {
            if (! $this->isBatchActive) {
                throw new Exception('No pipeline/batch operation currentl in progress');
            }

            $return = $name ? parent::exec($name) : parent::exec();
            $this->isBatchActive = false;
            return $return ?: [];
        }

        /**
         * Is there an open pipeline (batch) operation?
         *
         * @return bool
         */
        public function isBatchActive() {
            return $this->isBatchActive;
        }
    }