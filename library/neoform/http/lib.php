<?php

    namespace neoform\http;

    use neoform\http;

    class lib {

        /**
         * Checks the http segments, if they don't match the $segment_regex then user is redirected to a 404 page
         *
         * @param string $segment_regex
         * @param array  $permitted_gets GET parameter names that are allowed
         *
         * @return bool true on 404
         */
        public static function limit_url($segment_regex, array $permitted_gets=[]) {
            $segments = http::instance()->segments();
            array_shift($segments); // we don't want to have root be part of this
            if (! preg_match($segment_regex, '/' . join('/', $segments))) {
                controller::show404();
                return true;
            }

            // If any get values exist that aren't permitted, 404.
            if (array_diff_key(http::instance()->gets(), array_flip($permitted_gets))) {
                controller::show404();
                return true;
            }

            return false;
        }
    }