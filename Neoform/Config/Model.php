<?php

    namespace Neoform\Config;

    use Neoform;

    /**
     * Default config object - extend this to define config values for an entity
     *
     * @immutable do not change config values at runtime, that's poor coding technique.
     */
    abstract class Model {

        /**
         * @var static
         */
        private static $instance = [];

        /**
         * @var array
         */
        protected $values;

        /**
         * @param array $values
         */
        final public function __construct(array $values) {
            $this->values = $values;
        }

        /**
         * @return static
         * @throws Neoform\Config\Exception
         */
        final public static function get() {
            if (! isset(self::$instance[static::class])) {
                self::$instance[static::class] = Neoform\Core::get()->getEnvironment()->getConfig(static::class);
            }

            return self::$instance[static::class];
        }

        /**
         * @return array
         */
        final public function toArray() {
            return $this->values;
        }
    }