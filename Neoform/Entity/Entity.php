<?php

    namespace Neoform\Entity;

    interface Entity {

        /**
         * @return string
         */
        public static function getLabel();

        /**
         * @return string
         */
        public static function getSourceIdentifier();

        /**
         * @return string
         */
        public static function getNamespace();

        /**
         * @return string
         */
        public static function getCacheKeyPrefix();
    }