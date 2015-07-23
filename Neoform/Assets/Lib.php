<?php

    namespace Neoform\Assets;

    class Lib {

        /**
         * Compile assets and save to disk
         */
        public static function compile() {
            (new Dao)->compile();
        }
    }