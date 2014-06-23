<?php

    namespace neoform\http;

    use neoform\http;

    class lib {

        /**
         * Checks the http slugs, if they don't match the $slug_regex then user is redirected to a 404 page
         *
         * @param string $controller_slug_regex
         * @param string $non_controller_slug_regex
         * @param array  $permitted_gets            GET parameter names that are allowed
         * @param array  $permitted_parameters      /var1:val1/var2:val2/ parameter names that are allowed
         *
         * @return bool true on 404
         */
        public static function enforce_url($controller_slug_regex=null, $non_controller_slug_regex=null,
                                         array $permitted_gets=null, array $permitted_parameters=null) {

            if ($controller_slug_regex !== null) {
                $slugs = http::instance()->controller_slugs();
                array_shift($slugs); // we don't want to have root be part of this
                if (! preg_match($controller_slug_regex, '/' . join('/', $slugs))) {
                    controller::show404();
                    return true;
                }
            }

            if ($non_controller_slug_regex !== null) {
                if (! preg_match($non_controller_slug_regex, join('/', http::instance()->non_controller_slugs()))) {
                    controller::show404();
                    return true;
                }
            }

            // If any get values exist that aren't permitted, 404.
            if ($permitted_gets !== null) {
                if (array_diff_key(http::instance()->gets(), array_flip($permitted_gets + ['rc']))) {
                    controller::show404();
                    return true;
                }
            }

            // If any get values exist that aren't permitted, 404.
            if ($permitted_parameters !== null) {
                if (array_diff_key(http::instance()->parameters(), array_flip($permitted_parameters))) {
                    controller::show404();
                    return true;
                }
            }

            return false;
        }

        /**
         * Checks the http slugs, if they don't match the $slug_regex then user is redirected to a 404 page
         *
         * @param string $slug_regex
         * @param array  $permitted_gets       GET parameter names that are allowed
         * @param array  $permitted_parameters /var1:val1/var2:val2/ parameter names that are allowed
         *
         * @return bool true on 404
         */
        public static function enforce_url_custom($slug_regex=null, array $permitted_gets=null,
                                                  array $permitted_parameters=null) {

            if ($slug_regex !== null) {
                $slugs = http::instance()->slugs();
                array_shift($slugs);
                if (! preg_match($slug_regex, '/' . join('/', $slugs))) {
                    return true;
                }
            }

            // If any get values exist that aren't permitted, 404.
            if ($permitted_gets !== null) {
                if (array_diff_key(http::instance()->gets(), array_flip($permitted_gets + ['rc']))) {
                    return true;
                }
            }

            // If any get values exist that aren't permitted, 404.
            if ($permitted_parameters !== null) {
                if (array_diff_key(http::instance()->parameters(), array_flip($permitted_parameters))) {
                    return true;
                }
            }

            return false;
        }
    }