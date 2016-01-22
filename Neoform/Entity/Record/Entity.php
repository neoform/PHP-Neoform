<?php

    namespace Neoform\Entity\Record;

    use Neoform;

    interface Entity extends Neoform\Entity\Entity {

        /**
         * @return bool
         */
        public static function isPrimaryKeyAutoIncrement();

        /**
         * @return string
         */
        public static function getPrimaryKeyName();
    }