<?php

    namespace neoform;

    class web_lib {

        public static function wget($url, array $post=null, $bind_to_ip=null) {

            $curl = \curl_init($url);

            \curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux i686; pl-PL; rv:1.9.0.2) Gecko/20121223 Ubuntu/9.25 (jaunty) Firefox/3.8');
            \curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            //\curl_setopt($pCurl, CURLOPT_FOLLOWLOCATION, true);
            \curl_setopt($curl, CURLOPT_TIMEOUT, 20);

            if ($bind_to_ip) {
                \curl_setopt($curl, CURLOPT_INTERFACE, $bind_to_ip);
            }

            if (\count($post)) {
                \curl_setopt($curl, CURLOPT_POST, true);
                \curl_setopt($curl, CURLOPT_POSTFIELDS, \http_build_query($post));
            }

            $contents = \curl_exec($curl);
            $info = \curl_getinfo($curl);

            if ($info['http_code'] === 200) {
                return $contents;
            }

            throw new web_exception('Server returned HTTP/' . $info['http_code']);
        }
    }
