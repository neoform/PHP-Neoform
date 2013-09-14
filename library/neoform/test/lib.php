<?php

    namespace neoform\test;

    class lib {

        /**
         * Times the execution of a closure
         *
         * @param callable $f closure to be timed
         * @param string   $name
         * @param bool     $echo
         *
         * @return float
         */
        public static function timer(callable $f, $name, $echo=false) {
            if ($echo) {
                echo "Test: {$name}\n";
            }

            $t = microtime(1);
            $f();
            $t = microtime(1) - $t;

            if ($echo) {
                echo "Seconds: {$t}\n";
            }

            return $t;
        }
    }