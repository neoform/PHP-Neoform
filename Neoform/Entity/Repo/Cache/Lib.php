<?php

    namespace Neoform\Entity\Repo\Cache;

    use Neoform;

    class Lib {

        /**
         * @param string $engineClass
         * @param string $enginePool
         *
         * @return Driver
         */
        public static function getRepo($engineClass, $enginePool) {

            switch ((string) $engineClass) {
                case 'Redis':
                    return new Neoform\Entity\Repo\Cache\Driver\Redis(Neoform\Redis::getService($enginePool));

                case 'Memcache':
                    return new Neoform\Entity\Repo\Cache\Driver\Memcache(Neoform\Memcache::getService($enginePool));

                case 'Memory':
                    return Neoform\Entity\Repo\Cache\Driver\Memory::getInstance($enginePool);

                default:
                    return Neoform\Entity\Repo\Cache\Driver\None::getInstance($enginePool);
            }
        }
    }