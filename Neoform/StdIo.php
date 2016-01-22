<?php

    namespace Neoform;

    use SplDoublyLinkedList;

    class StdIo {

        /**
         * @param string $str
         */
        public function writeOut($str) {
            fwrite(STDOUT, $str);
        }

        /**
         * @param string $str
         */
        public function writeErr($str) {
            fwrite(STDERR, $str);
        }

        /**
         * @param $blockingIO
         *
         * @return string
         */
        public function readIn($blockingIO) {
            $f = fopen('php://stdin', 'r');
            stream_set_blocking($f, (bool) $blockingIO);
            $buffer = [];
            $buffer[] = fread($f, 1024);
            return join($buffer);
        }
    }
