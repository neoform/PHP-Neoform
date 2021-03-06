<?php

    namespace Neoform\Type\String;

    class Lib {

        /**
         * @param string $ip_str
         *
         * @return int
         */
        public static function ip2long($ip_str) {
            return (int) sprintf('%u', ip2long($ip_str));
        }

        /**
         * Same as nl2br, but with <p> instead of <br>
         *
         * @param string $str
         *
         * @return string
         */
        public static function nl2p($str) {
            return preg_replace('`(.[^\n]*)`', '<p>\1</p>', $str);
        }

        /**
         * Find any URLs in a body of text and convert them into anchor tagged links
         *
         * @param string     $str
         * @param array|null $attrs
         *
         * @return string
         */
        public static function urled($str, array $attrs=null) {

            $attrs = [];
            foreach ($attrs as $name => $val) {
                $attrs[] = "{$name}=\"{$val}\"";
            }
            $attrs = $attrs ? ' ' . join(' ', $attrs) : '';

            //Remove UTF-8 BOM and marker character in input (BOM is a blank char that is not actually blank, it's just a non-existent spacer...)
            $str = preg_replace('{^\xEF\xBB\xBF|\x1A}', '', $str);

            $regex = '`
                (?:
                    (?:http|https)\://
                    (?:[\.a-z0-9_\-\?#&\+\|\(\/)%@=;:]*)
                )
                |
                (?:
                    (?:www\.[\.a-z0-9_\-\?#&\+\|\(\/)%@=;:]*)
                )
                `ixs';

            if (preg_match_all($regex, $str, $urls)) {
                $urls = current($urls);

                $pickles = [];

                foreach ($urls as $url) {

                    $hash = sha1($url);
                    $http = strtolower(substr($url, 0, 4)) !== 'http' ? 'http://' : '';
                    $pickles[$hash] = '<a href="' . htmlspecialchars($http . $url) . '"{$attrs}>' . htmlspecialchars($url) . '</a>';

                    self::replace_once(
                        $str,
                        $url,
                        $hash
                    );
                }

                return str_replace(array_keys($pickles), array_values($pickles), htmlspecialchars($str));
            }

            return $str;
        }

        /**
         * @param string $str
         * @param string $search
         * @param string $replace
         */
        protected static function replace_once(& $str, $search, $replace) {
            $pos = strpos($str, $search);

            if ($pos !== false) {
                $search_len = strlen($search);
                $str        = substr($str, 0, $pos) . $replace . substr($str, $pos + $search_len);
            }
        }

        /**
         * Return the English suffix of a number
         *
         * @param integer $num
         *
         * @return string
         */
        public static function nth($num) {
            switch (substr($num, strlen($num) - 1, 1)) {
                case '1':
                    return 'st';
                case '2':
                    return 'nd';
                case '3':
                    return 'rd';
            }

            return 'th';
        }

        /**
         * If max length not set, it will be fixed to the min length
         *
         * @param integer      $min_length
         * @param integer|null $max_length
         *
         * @return string
         */
        public static function random_chars($min_length, $max_length=null) {
            static $letters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

            $len   = $max_length === null ? $min_length : mt_rand($min_length, $max_length);
            $chars = [];

            for ($i=0; $i < $len; $i++) {
                $chars[] = $letters[mt_rand(0, 61)];
            }

            return implode($chars);
        }

        /**
         * Replace strings with funky chars into non-accent versions
         *
         * @param string $str
         *
         * @return string
         */
        public static function remove_accents($str) {
            $chars = [
                'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
                'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
                'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
                'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
                'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
                'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
                'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f'
            ];
            return str_replace(array_keys($chars), array_values($chars), $str);
        }

        /**
         * Convert an integer into a shorter string (useful for case sensitive URLs)
         *
         * @param integer $n
         *
         * @return string
         */
        public static function shorten($n)
        {
            static $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

            $str = '';
            while ($n > 0) {
                $remainder = $n % 62;
                $n         = ($n - $remainder) / 62;
                $str       = $alphabet{$remainder} . $str;
            }

            return $str;
        }

        /**
         * Convert a type_string_lib::shortened() string into its integer counterpart
         *
         * @param string $str
         *
         * @return integer
         */
        public static function expand($str)
        {
            static $alphabet = [
                'a' => 0,  'b' => 1,  'c' => 2,  'd' => 3,  'e' => 4,  'f' => 5,  'g' => 6,  'h' => 7,  'i' => 8,  'j' => 9,
                'k' => 10, 'l' => 11, 'm' => 12, 'n' => 13, 'o' => 14, 'p' => 15, 'q' => 16, 'r' => 17, 's' => 18, 't' => 19,
                'u' => 20, 'v' => 21, 'w' => 22, 'x' => 23, 'y' => 24, 'z' => 25,
                'A' => 26, 'B' => 27, 'C' => 28, 'D' => 29, 'E' => 30, 'F' => 31, 'G' => 32, 'H' => 33, 'I' => 34, 'J' => 35,
                'K' => 36, 'L' => 37, 'M' => 38, 'N' => 39, 'O' => 40, 'P' => 41, 'Q' => 42, 'R' => 43, 'S' => 44, 'T' => 45,
                'U' => 46, 'V' => 47, 'W' => 48, 'X' => 49, 'Y' => 50, 'Z' => 51,
                '0' => 52, '1' => 53, '2' => 54, '3' => 55, '4' => 56, '5' => 57, '6' => 58, '7' => 59, '8' => 60, '9' => 61,
            ];

            $n = 0;
            $len = strlen($str);
            for ($i=0; $i < $len; $i++) {
                $c = $str{$i};
                if (! isset($alphabet[$c])) {
                    return null;
                }
                $n = $n * 62 + $alphabet[$c];
            }

            return $n;
        }

        /**
         * Takes two colors and a percentage, then returns a color based on that percent.
         * Eg: 'ffffff' and '000000' 50% would be half way between those two colors
         *
         * @param string|array $color1 either a hex string or an array with RGB values
         * @param string|array $color2 either a hex string or an array with RGB values
         * @param integer      $percent 0-100
         * @param bool         $hex
         *
         * @return array|string
         * @throws \Exception
         */
        public static function color_percent($color1, $color2, $percent, $hex=true) {

            if ($percent > 100 || $percent < 0) {
                throw new \Exception('Percentage cannot be less than 0 or greater than 100');
            }

            $percent /= 100;

            if (is_array($color1)) {
                list($r1, $g1, $b1) = $color1;
            } else {
                $r1 = (int) hexdec(substr($color1, 0, 2));
                $g1 = (int) hexdec(substr($color1, 2, 2));
                $b1 = (int) hexdec(substr($color1, 4, 2));
            }

            if (is_array($color2)) {
                list($r2, $g2, $b2) = $color2;
            } else {
                $r2 = (int) hexdec(substr($color2, 0, 2));
                $g2 = (int) hexdec(substr($color2, 2, 2));
                $b2 = (int) hexdec(substr($color2, 4, 2));
            }

            if ($hex) {
                return str_pad(dechex($r1 > $r2 ? abs(($r1 - $r2) * $percent - $r1) : ($r2 - $r1) * $percent), 2, '0')
                       . str_pad(dechex($g1 > $g2 ? abs(($g1 - $g2) * $percent - $g1) : ($g2 - $g1) * $percent), 2, '0')
                       . str_pad(dechex($b1 > $b2 ? abs(($b1 - $b2) * $percent - $b1) : ($b2 - $b1) * $percent), 2, '0');
            } else {
                return [
                    $r1 > $r2 ? abs(($r1 - $r2) * $percent - $r1) : ($r2 - $r1) * $percent,
                    $g1 > $g2 ? abs(($g1 - $g2) * $percent - $g1) : ($g2 - $g1) * $percent,
                    $b1 > $b2 ? abs(($b1 - $b2) * $percent - $b1) : ($b2 - $b1) * $percent,
                ];
            }
        }

        /**
         * @param string $haystack
         * @param string $needle
         *
         * @return bool
         */
        public static function starts_with($haystack, $needle) {
            return (substr($haystack, 0, strlen($needle)) === $needle);
        }

        /**
         * @param string $haystack
         * @param string $needle
         *
         * @return bool
         */
        public static function ends_with($haystack, $needle) {
            $length = strlen($needle);

            if (! $length) {
                return true;
            }

            return (substr($haystack, -$length) === $needle);
        }

        /**
         * @param string $str
         * @param int    $length
         * @param bool   $clip
         *
         * @return string
         */
        public static function forcedWidth($str, $length, $clip=true) {
            if (strlen($str) <= $length) {
                return str_pad($str, $length);
            }

            if ($clip) {
                return substr($str, $length);
            }

            return $str;
        }
    }




