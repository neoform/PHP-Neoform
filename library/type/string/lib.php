<?php

    class type_string_lib {

        public static function bin2hex($str) {
            return bin2hex((string) $str);
        }

        public static function hex2bin($str) {
            return pack('H*', (string) $str);
        }

        public static function formatted($str) {
            return self::nl2p(self::urled($str));
        }

        public static function nl2p($str) {
            return preg_replace('`(.[^\n]*)`', '<p>\1</p>', $str);
        }

        public static function phone($str) {
            if (strlen($str) === 10) {
                return '(' . substr($str, 0, 3) . ') ' . substr($str, 3, 3) . '-' . substr($str, 6, 4);
            } else {
                return substr($str, 0, 1) . '-' . substr($str, 1, 3) . '-' . substr($str, 4, 3) . '-' . substr($str, 7, 4);
            }
        }

        public static function postal_code($str) {
            return substr($str, 0, 3) . ' ' . substr($str, 3, 3);
        }

        public static function urled($str) {

            //people should not be using this keyword... everrr
            //$str = str_ireplace('javascript:', 'javascrap:', $str);

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
                    $pickles[$hash] = '<a href="' . htmlspecialchars($http . $url) . '" rel="nofollow">' . htmlspecialchars($url) . '</a>';

                    self::replace_once(
                        $str,
                        $url,
                        $hash
                    );
                }

                $str = str_replace(array_keys($pickles), array_values($pickles), htmlspecialchars($str));
            }

            return $str;
        }

        protected static function replace_once(& $str, $search, $replace) {
            $pos = strpos($str, $search);

            if ($pos !== false) {
                $search_len = strlen($search);
                $str = substr($str, 0, $pos) . $replace . substr($str, $pos + $search_len);
               }
        }

        public static function nth($num) {
            $last = substr($num, strlen($num) - 1, 1);

            switch ($last) {
                case '1':
                    return 'st';
                case '2':
                    return 'nd';
                case '3':
                    return 'rd';
                default:
                    return 'th';
            }
        }

        //if max length not set, it will be fixed to the min length
        public static function random_chars($min_length, $max_length=null) {
            static $letters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

            $len = $max_length === null ? $min_length : mt_rand($min_length, $max_length);

            $path = '';
            for ($i=0; $i < $len; $i++) {
                $path .= $letters[mt_rand(0, 61)];
            }

            return $path;
        }

        public static function remove_accents($str) {
            $chars = [
                'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
                'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
                'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
                'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
                'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
                'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
                'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f'
            ];
            return str_replace(array_keys($chars), array_values($chars), $str);
        }

        public static function pg13($str) {

            static $search_arr;
            static $replace_arr;

            if ($search_arr === null) {
                $d = '([\s\.\_\-\*]*)';

                $words = [
                    'bitch' => [
                        'ditch',
                        'female dog',
                    ],
                    'cock' => [
                        'weiner',
                        'peen',
                        'little thingy',
                    ],
                    'cum' => [
                        'bum',
                        'rum',
                        'yum',
                        'man fluids',
                    ],
                    'cunt' => [
                        'runt',
                        'stunt',
                        'bunt',
                    ],
                    'faggot' => [
                        'biggot',
                        'delightful fellow',
                        'splendid fellow',
                        'colorful chap',
                        'enjoyable fellow',
                    ],
                    'fag' => [
                        'pink handbag',
                        'bag',
                    ],
                    'fuck' => [
                        'fark',
                        'bark',
                        'frak',
                        'fork',
                        'frik',
                        'darn',
                    ],
                    'jizz' => [
                        'jazz',
                        'glue',
                        'beer',
                        'fruit juice',
                    ],
                    'motherfucker' => [
                        'mother loving',
                    ],
                    'motherfucking' => [
                        'mother loving',
                    ],
                    'nigger' => [
                        'nagger',
                        'bagger',
                        'blogger',
                        'jolly black chap',
                        'delightful black fellow',
                    ],
                    'piss' => [
                        'mountain dew',
                        'crab juice',
                    ],
                    'retarded' => [
                        'special',
                        'amazing',
                        'fantastic',
                        'stupendous',
                    ],
                    'retard' => [
                        'great guy',
                        'amazing person',
                        'special dude',
                        'hamburger',
                    ],
                    'shit' => [
                        'crap',
                        'crud',
                    ],
                    'slut' => [
                        'harlot',
                        'hussy',
                        'wench',
                        'strumpet',
                    ],
                    'whore' => [
                        'adorable lady',
                        'promiscuous female',
                        'alluring woman',
                        'fair maiden',
                    ],
                ];
                $search_arr  = [];
                $replace_arr = [];
                foreach ($words as $search => $replace) {
                    $search_arr[]     = '`(^|[\s\.\,\-])' . join($d, str_split($search)) . '`is';
                    $replace = str_split($replace[mt_rand(0, count($replace) - 1)]);
                    $replace_regex = '\1';
                    foreach ($replace as $i => $c) {
                        $replace_regex .= $c . '\\' . ($i + 2);
                    }
                    $replace_arr[] = '<span class="a">' . $replace_regex . '</span>';
                }
            }

            return preg_replace($search_arr, $replace_arr, $str);
        }

        public static function shorten($n)
        {
            static $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';

            $str = '';
            while ($n > 0) {
                $remainder = $n % 64;
                $n = ($n - $remainder) / 64;
                $str = $alphabet{$remainder} . $str;
            }

            return $str;
        }

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
                '_' => 62, '-' => 63,
            ];

            $n = 0;
            $len = strlen($str);
            for ($i=0; $i < $len; $i++) {
                $c = $str{$i};
                $n = $n * 64 + $alphabet[$c];
            }

            return $n;
        }
    }




