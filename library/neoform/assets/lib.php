<?php

    namespace neoform\assets;

    class lib {

        /**
         * Compile assets and save to disk
         */
        public static function compile() {
            (new dao)->compile();
        }
    }