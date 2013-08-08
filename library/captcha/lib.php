<?php

    class captcha_lib {

        //const PUBLIC_KEY     = '6LfbAb4SAAAAAFkOr2eD8CtgKKnNN0ZoBJ4rmb_q'; // .rankroll.com
        //const PRIVATE_KEY     = '6LfbAb4SAAAAABC5YXgyl0Q6BvJxzE-39rrMGnCt'; //prod

        //const PUBLIC_KEY     = '6Lf-yL0SAAAAAGUms1_LJfUuPxCokp8-RCPFZupr'; //sandbox
        //const PRIVATE_KEY     = '6Lf-yL0SAAAAAK3Zn-4rZeOX_7rrDkYZ1vCdteDw'; //sandbox

        //const EMAIL_PUBLIC_KEY     = '013CXgHD9rlruHc9AddJl-mg=='; //sandbox
        //const EMAIL_PRIVATE_KEY = '4a02c4e3fb7bac62e74136f7777ee458'; //sandbox

        const RECAPTCHA_API_SERVER            = "http://www.google.com/recaptcha/api";
        const RECAPTCHA_API_SECURE_SERVER     = "https://www.google.com/recaptcha/api";
        const RECAPTCHA_VERIFY_SERVER         = "www.google.com";

        public static function public_key() {
            return core::config('recaptcha')->api['public'];
        }

        //only run this once the user has entered a valid captcha - validity of session is 60 seconds
        public static function session_make() {
            core::http_flash()->set('captcha', 1, 60);
        }

        public static function session_validate() {

            $return = (bool) core::http_flash()->get('captcha');
            if ($return) {
                core::http_flash()->del('captcha');
            }

            return $return;
        }

        /**
         * Encodes the given data into a query string format
         * @param $data - array of string elements to be encoded
         * @return string - encoded request
         */
        protected static function _recaptcha_qsencode($data) {
            $req = "";
            foreach ($data as $key => $value) {
                $req .= $key . '=' . urlencode(stripslashes($value)) . '&';
            }

            // Cut the last '&'
            $req = substr($req, 0, strlen($req) - 1);
            return $req;
        }

        /**
         * Submits an HTTP POST to a reCAPTCHA server
         * @param string $host
         * @param string $path
         * @param array $data
         * @param int port
         * @return array response
         */
        protected static function _recaptcha_http_post($host, $path, $data, $port = 80) {

            $req = self::_recaptcha_qsencode($data);

            $http_request  = "POST " . $path . " HTTP/1.0\r\n";
            $http_request .= "Host: " . $host . "\r\n";
            $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
            $http_request .= "Content-Length: " . strlen($req) . "\r\n";
            $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
            $http_request .= "\r\n";
            $http_request .= $req;

            $response = '';
            if (false == ($fs = @fsockopen($host, $port, $errno, $errstr, 10))) {
                throw new exception('Could not open socket');
            }

            fwrite($fs, $http_request);

            while (! feof($fs)) {
                $response .= fgets($fs, 1160); // One TCP-IP packet
            }

            fclose($fs);
            $response = explode("\r\n\r\n", $response, 2);

            return $response;
        }



        /**
         * Gets the challenge HTML (javascript and non-javascript version).
         * This is called from the browser, and the resulting reCAPTCHA HTML widget
         * is embedded within the HTML form it was called from.
         * @param string $pubkey A public key for reCAPTCHA
         * @param string $error The error given by reCAPTCHA (optional, default is null)
         * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)

         * @return string - The HTML to be embedded in the user's form.
         */
        public static function recaptcha_get_html($error=null, $use_ssl=false) {

            //if ($pubkey == null || $pubkey == '') {
            //    die ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
            //}

            if ($use_ssl) {
                $server = self::RECAPTCHA_API_SECURE_SERVER;
            } else {
                $server = self::RECAPTCHA_API_SERVER;
            }

            $errorpart = "";

            if ($error) {
               $errorpart = "&amp;error=" . $error;
            }

            return '<script type="text/javascript" src="'. $server . '/challenge?k=' . core::config('recaptcha')->api['public'] . $errorpart . '"></script>

            <noscript>
                  <iframe src="'. $server . '/noscript?k=' . core::config('recaptcha')->api['public'] . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br/>
                  <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
                  <input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
            </noscript>';
        }

        /**
         * Calls an HTTP POST function to verify if the user's guess was correct
         *
         * @param string $remoteip
         * @param string $challenge
         * @param string $response
         * @param array  $extra_params
         *
         * @return ReCaptchaResponse
         * @throws exception
         */
        public static function recaptcha_check_answer($remoteip, $challenge, $response, $extra_params = []) {
            if ($remoteip == null || $remoteip == '') {
                throw new exception("For security reasons, you must pass the remote ip to reCAPTCHA");
            }

            //discard spam submissions
            if ($challenge == null || strlen($challenge) === 0 || $response === null || strlen($response) === 0) {
                $recaptcha_response = new ReCaptchaResponse();
                $recaptcha_response->is_valid = false;
                $recaptcha_response->error = 'incorrect-captcha-sol';
                return $recaptcha_response;
            }

            $response = self::_recaptcha_http_post(
                self::RECAPTCHA_VERIFY_SERVER,
                "/recaptcha/api/verify",
                [
                    'privatekey'     => core::config('recaptcha')->api['private'],
                    'remoteip'         => $remoteip,
                    'challenge'     => $challenge,
                    'response'         => $response,
                ] + $extra_params
            );

            $answers = explode ("\n", $response[1]);
            $recaptcha_response = new ReCaptchaResponse();

            if (trim($answers [0]) == 'true') {
                $recaptcha_response->is_valid = true;
            } else {
                $recaptcha_response->is_valid = false;
                $recaptcha_response->error = $answers[1];
            }

            return $recaptcha_response;
        }

        /**
         * Gets a URL where the user can sign up for reCAPTCHA. If your application
         * has a configuration page where you enter a key, you should provide a link
         * using this function.
         *
         * @param string|null $domain The domain where the page is hosted
         * @param string|null $appname The name of your application
         *
         * @return string
         */
        public static function recaptcha_get_signup_url($domain=null, $appname=null) {
            return "https://www.google.com/recaptcha/admin/create?" .  self::_recaptcha_qsencode([
                'domains' => $domain,
                'app'     => $appname,
            ]);
        }

        /**
         * @param $val
         *
         * @return string
         */
        protected static function _recaptcha_aes_pad($val) {
            $block_size = 16;
            $numpad = $block_size - (strlen ($val) % $block_size);
            return str_pad($val, strlen ($val) + $numpad, chr($numpad));
        }

        /**
         * @param $val
         * @param $ky
         *
         * @return string
         * @throws exception
         */
        protected static function _recaptcha_aes_encrypt($val, $ky) {
            if (! function_exists("mcrypt_encrypt")) {
                throw new exception("To use reCAPTCHA Mailhide, you need to have the mcrypt php module installed.");
            }
            $mode = MCRYPT_MODE_CBC;
            $enc = MCRYPT_RIJNDAEL_128;
            $val = self::_recaptcha_aes_pad($val);
            return mcrypt_encrypt($enc, $ky, $val, $mode, "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0");
        }

        /**
         * @param $x
         *
         * @return string
         */
        protected static function _recaptcha_mailhide_urlbase64($x) {
            return strtr(base64_encode($x), '+/', '-_');
        }

        /**
         *  Gets the reCAPTCHA Mailhide url for a given email, public key and private key
         *
         * @param string $email
         *
         * @return string
         */
        public static function recaptcha_mailhide_url($email) {
            //if ($pubkey == '' || $pubkey == null || $privkey == "" || $privkey == null) {
            //    throw new exception_recaptcha(
            //        "To use reCAPTCHA Mailhide, you have to sign up for a public and private key, " .
            //        "you can do so at <a href='http://www.google.com/recaptcha/mailhide/apikey'>".
            //        "http://www.google.com/recaptcha/mailhide/apikey</a>"
            //    );
            //}

            $ky = @pack('H*', self::EMAIL_PRIVATE_KEY);
            $cryptmail = self::_recaptcha_aes_encrypt($email, self::EMAIL_PRIVATE_KEY);

            return "http://www.google.com/recaptcha/mailhide/d?k=" . core::config('recaptcha')->api['public'] . "&c=" . self::_recaptcha_mailhide_urlbase64($cryptmail);
        }

        /**
         * Gets the parts of the email to expose to the user.
         * eg, given johndoe@example,com return ["john", "example.com"].
         * the email is then displayed as john...@example.com
         *
         * @param $email
         *
         * @return array
         */
        protected static function _recaptcha_mailhide_email_parts($email) {

            $arr = preg_split("/@/", $email );

            if (strlen ($arr[0]) <= 4) {
                $arr[0] = substr ($arr[0], 0, 1);
            } else if (strlen ($arr[0]) <= 6) {
                $arr[0] = substr ($arr[0], 0, 3);
            } else {
                $arr[0] = substr ($arr[0], 0, 4);
            }
            return $arr;
        }

        /**
         * Gets html to display an email address given a public an private key.
         * to get a key, go to:
         *
         * http://www.google.com/recaptcha/mailhide/apikey
         *
         * @param $email
         *
         * @return string
         */
        public static function recaptcha_mailhide_html($email) {
            $emailparts = self::_recaptcha_mailhide_email_parts($email);
            $url = self::recaptcha_mailhide_url(self::EMAIL_PUBLIC_KEY, self::EMAIL_PRIVATE_KEY, $email);

            return htmlentities($emailparts[0]) . "<a href='" . htmlentities ($url) .
                "' onclick=\"window.open('" . htmlentities ($url) . "', '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300'); return false;\" title=\"Reveal this e-mail address\">...</a>@" . htmlentities($emailparts[1]);

        }
    }

    /**
     * A ReCaptchaResponse is returned from recaptcha_check_answer()
     */
    class ReCaptchaResponse {
        public $is_valid;
        public $error;
    }