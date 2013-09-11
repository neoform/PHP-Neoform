<?php

    namespace neoform\render;

    use neoform\core;

    class view {

        protected $__vars;
        protected $__locked;

        const VIEW_EXT = 'phtml';
        const JS_EXT   = 'js';

        public function __construct(array $preload_vars=[]) {
            $this->__vars = $preload_vars;
            $this->__vars['locale'] = core::locale();
        }

        // Render view and return contents - no header modification
        public function html($view) {
            if (! $view) {
                return '';
            }

            $this->__locked = true;

            ob_start();

            $this->inc($view);

            try {
                $buffer = '';
                while (ob_get_length()) {
                    $buffer .= ob_get_clean();
                }
                return $buffer;
            } catch (\exception $e) {
                throw new \exception('Output buffer error occurred');
            }
        }

        public static function js($js_view) {
            $js_view = core::path('application') . "/dialogs/{$js_view}." . self::JS_EXT;

            if (file_exists($js_view))    {
                try {
                    return file_get_contents($js_view);
                } catch (\exception $e) {
                    throw new \exception('Error occured while reading JS file');
                }
            }
        }

        // Render a view, return contents
        public function execute($view, $encoding=null) {

            $body = $this->html($view);

            $output = core::output();

            if ($encoding !== null) {
                if ($encoding === 'deflate') {
                    $output->header('Content-Encoding', 'deflate');
                    $body = gzdeflate($body);
                    $output->header('Content-Length', strlen($body));
                } else if ($encoding === 'gzip') {
                    $output->header('Content-Encoding', 'gzip');
                    $body = gzcompress($body, 9);
                    $output->header('Content-Length', strlen($body));
                    //$output = "\x1f\x8b\x08\x00\x00\x00\x00\x00" . $output;
                }
            }

            $output->send_headers();
            echo $body;
        }

        // Render a view, send contents to core::output()
        public function render($view, $encoding=null) {

            $body = $this->html($view);

            $output = core::output();

            if ($encoding !== null) {
                if ($encoding === 'deflate') {
                    $output->header('Content-Encoding', 'deflate');
                    $body = gzdeflate($body);
                    $output->header('Content-Length', strlen($body));
                } else if ($encoding === 'gzip') {
                    $output->header('Content-Encoding', 'gzip');
                    $body = gzcompress($body, 9);
                    $output->header('Content-Length', strlen($body));
                    //$output = "\x1f\x8b\x08\x00\x00\x00\x00\x00" . $output;
                }
            }

            $output->body($body);
        }

        public function __get($k) {
            if (isset($this->__vars[$k])) {
                return $this->__vars[$k];
            }
        }

        public function __set($k, $v) {
            // don't allow any changing vars in a view once execution starts
            // code/logic/assignments should not take place in a view.
            if (! $this->__locked && $k != 'this') {
                $this->__vars[$k] = $v;
            } else {
                throw new \exception('Cannot assign a value to variable "$' . $k . '" from within a templace once its executed.');
            }
        }

        protected function variables() {
            return array_keys($this->__vars);
        }

        protected function inc($__template, array $__args=null) {
            $__path = core::path('application') . "/views/{$__template}." . self::VIEW_EXT;

            if (file_exists($__path))    {
                if ($__args !== null) {
                    extract($__args);
                }
                require($__path);
            } else {
                throw new \exception('Could not load view template file "' . $__template . '"');
            }
        }
    }

