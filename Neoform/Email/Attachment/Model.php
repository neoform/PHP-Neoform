<?php

    namespace Neoform\Email\Attachment;

    use Neoform;

    class Model {

        // set variables used here
        protected $html_body;
        protected $text_body;
        protected $attachments        = [];
        protected $attachments_encode = [];
        protected $body;
        protected $subject;
        protected $headers;

        // this will attach a file to the email
        public function attach($name, $f, $already_base64=false) {

            if (! $f) {
                return;
            }

            $name = trim(str_replace('"', '', $name));

            // if alredy put in base64, don't do this.  This is used when we base64 encode it on the command line for speed.
            if (!$already_base64) {
                $this->attachments[$name]        = chunk_split(base64_encode($f), 76, "\n");
                $this->attachments_encode[$name] = "base64";
            } else {
                $this->attachments[$name]        = $f;
                $this->attachments_encode[$name] = "binary";
            }
        }

        // attaches the text email
        public function set_text($t) {
            $this->text_body = $t;
        }

        // attaches the html email
        public function set_html($h) {
            $this->html_body = $h;
        }

        // set header(s).  Notice it appends so you can call it multiple times.
        public function set_header($d) {
            $this->headers .= $d;
        }

        // set the subject
        public function set_subject($s) {
            $this->subject = $s;
        }

        /*
         * This function will actually send the email.
         *
         * $email - the recipient email.  MUST be a valid email.
         * $name - the recipient name, this is optional.
         * $debug - print out email source instead of sending the email.
         */
        public function send($email, $name = "") {

            // error check
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Neoform\Email\Exception('Email address "' . $email . '" is not valid');
            }

            if (! $this->text_body && ! $this->html_body) {
                throw new Neoform\Email\Exception("No email body (text or html) was set.");
            }

            // set the to email to include name if provided (php mail() funciton will take this format)
            if ($name) {
                $email = "{$name} <{$email}>";
            }

            $email_boundary = $this->generate_boundary();
            $body_boundary = $this->generate_boundary("{$this->text_body} {$this->html_body}");

            // set the multi-part MIME headers
            $this->headers .= "Content-class: urn:content-classes:message\n";
            $this->headers .= "MIME-Version: 1.0\n";
            $this->headers .= "Content-Type: multipart/mixed; boundary=\"{$email_boundary}\"\n";

            // write the body of the email.
            $this->body = "This is a multi-part message in MIME format.\n\n";
            $this->body .= "--{$email_boundary}\n";
            $this->body .= "Content-Type: multipart/alternative; boundary=\"{$body_boundary}\"\n\n";

            if($this->text_body != "") {
                $this->body .= "--{$body_boundary}\n";
                $this->body .= "Content-Type: text/plain; charset=utf-8; format=flowed\n";
                $this->body .= "Content-Transfer-Encoding: 7bit\n\n";
                $this->body .= "{$this->text_body}\n\n";
            }

            if($this->html_body!="") {
                $this->body .= "{$body_boundary}\n";
                $this->body .= "Content-Type: text/html; charset=utf-8\n";
                $this->body .= "Content-Transfer-Encoding: 7bit\n\n";
                $this->body .= "{$this->html_body}\n\n";
            }
            $this->body .= "--" . $body_boundary . "--\n\n";

            // if there is an email attachment, add that.
            foreach ($this->attachments as $k => $v) {
                // write info about file
                $this->body .= "--{$email_boundary}\n";
                $this->body .= "Content-Type: application/force-download; ";
                $this->body .= "name=\"{$k}\"\n";
                $this->body .= "Content-Transfer-Encoding: base64\n";
                $this->body .= "Content-Description: \"{$k}\"\n";
                $this->body .= "Content-Disposition: attachment; ";
                $this->body .= "filename=\"{$k}\"\n\n";
                // write attachment in lines of 76 characters (standard)
                $this->body .= $v;
                $this->body .= "\n";
            }
            $this->body .= "--{$email_boundary}--\n";

            // send the mail
            if (\mail($email, $this->subject, $this->body, $this->headers)) {
                return true;
            } else {
                throw new Neoform\Email\Exception("PHP mail function failed for unknown reason");
            }
        }

        protected function generate_boundary($body=null) {

            do {
                $boundary = '---' . md5(microtime(1) . mt_rand()) . '---';
            } while ($body !== null && strpos($body, $boundary) === false);

            return $boundary;
        }
    }