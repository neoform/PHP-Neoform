<?php

    namespace neoform\http;

    use neoform\http;

    class lib {

        /**
         * Checks the http slugs, if they don't match the $slug_regex then user is redirected to a 404 page
         *
         * @param string $slug_regex
         * @param array  $permitted_gets GET parameter names that are allowed
         *
         * @return bool true on 404
         */
        public static function limit_url($slug_regex, array $permitted_gets=[]) {
            $slugs = http::instance()->slugs();
            array_shift($slugs); // we don't want to have root be part of this
            if (! preg_match($slug_regex, '/' . join('/', $slugs))) {
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