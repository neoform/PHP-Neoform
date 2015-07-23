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
            stream_set_blocking(STDIN, (bool) $blockingIO);
            $buffer = new SplDoublyLinkedList;
            $buffer->push(fread(STDIN, 1024));
            return join($buffer);
        }
    }
