<?php

    namespace Neoform\Entity\Repo\LinkSource;

    use Neoform;

    class Lib {

        /**
         * @param Neoform\Entity\Dao $dao
         * @param string             $engineClass
         * @param string             $enginePoolRead
         * @param string             $enginePoolWrite
         *
         * @return Driver
         * @throws Neoform\Entity\Repo\Exception
         */
        public static function getRepo(Neoform\Entity\Dao $dao, $engineClass, $enginePoolRead, $enginePoolWrite) {

            switch ((string) $engineClass) {
                case 'MySQL':
                    return new Driver\MySQL(
                        $dao,
                        Neoform\Sql::getService($enginePoolRead),
                        Neoform\Sql::getService($enginePoolWrite)
                    );

                case 'PostgreSQL':
                    return new Driver\PostgreSQL(
                        $dao,
                        Neoform\Sql::getService($enginePoolRead),
                        Neoform\Sql::getService($enginePoolWrite)
                    );

                default:
                    throw new Neoform\Entity\Repo\Exception('No source driver specified');
            }
        }
    }