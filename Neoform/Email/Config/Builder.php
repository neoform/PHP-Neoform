<?php

    namespace Neoform\Email\Config;

    use Neoform;

    class Builder extends Neoform\Config\Builder {

        /**
         * @return Neoform\Email\Config
         */
        public function build() {
            return new Neoform\Email\Config($this->configValues);
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [
                // random string used to give users a hash (based on their user info) that they can disable their
                // email notifications
                'unsubscribe_secret' => null,
            ];
        }

        /**
         * Validate the config values
         *
         * @throws Neoform\Config\Exception
         */
        public function validate() {
            if (empty($this->configValues['unsubscribe_secret'])) {
                throw new Neoform\Config\Exception('"unsubscribe_secret" must be set');
            }
        }

        /**
         * Validate the config values after the config has been compiled
         *
         * @param Neoform\Config\Collection $configs
         */
        public function validatePost(Neoform\Config\Collection $configs) {

        }
    }