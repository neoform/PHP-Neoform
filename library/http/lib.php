<?php

    class http_lib {

        /**
         * Checks the http segments, if they don't match the $segment_regex then user is redirected to a 404 page
         *
         * @param string $segment_regex
         */
        public static function limit_url($segment_regex) {
            if (! preg_match($segment_regex, join('/', core::http()->segments()))) {
                core::output()->redirect('error/not_found', 301);
            }
        }
    }