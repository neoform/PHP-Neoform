<?php

    namespace Neoform\Email;

    use Neoform;

    class Model {

        protected $_vars;
        protected $_email_path;

        protected $_default_sender;

        //store these vars in the class so the template vars don't
        //override them
        protected $_type;
        protected $_headers;

        public function __construct($template, $label='default', $default_sender_email=null, $default_sender_name=null) {

            if ($default_sender_email && trim($default_sender_email)) {
                if ($default_sender_name) {
                    $this->_default_sender = $default_sender_email;
                } else {
                    $this->_default_sender = '=?UTF-8?B?' . base64_encode($default_sender_name) . "?= <{$default_sender_email}>";
                }
            } else {
                $this->_default_sender = '=?UTF-8?B?' . base64_encode(Neoform\Core\Config::get()->getSiteName()) . '?= <noreply@' . Neoform\Router\Config::get()->getDomain() . '>';
            }

            $this->_vars = [];

            if (strpos($template, '..') !== false) {
                $template = str_replace('..', '', $template);
            }

            if (strpos($label, '..') !== false) {
                $label = str_replace('..', '', $label);
            }

            $this->_email_path = Neoform\Core::get()->getApplicationPath() . "/emails/{$label}/{$template}.phtml";
        }

        public function send($recipient, $type='plain', $sender=null, array $headers=[]) {

            $this->_type = strtolower($type);

            $headers['MIME-Version'] = '1.0';
            $headers['Content-type'] = "text/{$this->_type}; charset=utf-8";

            $headers['From'] = $sender && trim($sender) ? $sender : $this->_default_sender;

            $this->_headers = '';
            foreach ($headers as $k => $v) {
                $this->_headers .= $k . ': ' . $v ."\r\n";
            }

            if (! $this->_email_path) {
                throw new Exception('Email path not set');
            }

            ob_start();

            try {
                require($this->_email_path);
            } catch (\Exception $e) {
                Neoform\Core::log($e->getMessage() . ' -- ' . $e->getFile() .' (' . $e->getLine() . ')');
            }

            try {
                $body = ob_get_clean();
            } catch (\Exception $e) {
                throw new Exception('Email could not be sent');
            }

            $subject = $html = $plain = '';

            // break appart the email template
            if (preg_match('`<email_head>(.*?)</email_head>`is', $body, $email_head)) {
                $subject = trim($email_head[1]);
            }

            if (preg_match('`<email_plain>(.*?)</email_plain>`is', $body, $email_plain)) {
                $plain = trim($email_plain[1]);
            }

            if (preg_match('`<email_html>(.*?)</email_html>`is', $body, $email_html)) {
                $html = trim($email_html[1]);
            }

            if ($this->_type == 'html' && trim($html)) {
                $body = $html;
            } else if ($plain) {
                $body = $plain; //wordwrap($plain, 70);
            }

            if ($this->_headers) {
                return mail($recipient, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $this->_headers);
            } else {
                return mail($recipient, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body);
            }
        }

        public function __get($k) {
            if (isset($this->_vars[$k])) {
                return $this->_vars[$k];
            }
        }

        public function __set($k, $v) {
            $this->_vars[$k] = $v;
        }
    }