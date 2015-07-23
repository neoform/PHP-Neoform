<?php

    namespace Neoform\Entity\Repo\MetaCache;

    use Neoform;

    class Lib {

        /**
         * @param string $engineClass
         * @param string $enginePool
         *
         * @return Neoform\Entity\Repo\MetaCache\Driver
         */
        public static function getRepo($engineClass, $enginePool) {

            switch ((string) $engineClass) {
                case 'Redis':
                    return new Neoform\Entity\Repo\MetaCache\Driver\Redis(Neoform\Redis::getService($enginePool));

                case 'Memory':
                    return Neoform\Entity\Repo\MetaCache\Driver\Memory::getInstance($enginePool);

                default:
                    return Neoform\Entity\Repo\MetaCache\Driver\None::getInstance($enginePool);
            }
        }
    }