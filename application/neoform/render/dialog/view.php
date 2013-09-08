<?php

    namespace neoform;

    class render_dialog_view {

        protected $__vars;
        protected $__html;

        const VIEW_EXT = 'phtml';

        public function __construct($__path, array $__vars) {
            $this->__vars           = $__vars;
            $this->__vars['locale'] = core::locale();

            $__path = core::path('application') . "/dialogs/{$__path}." . self::VIEW_EXT;

            \ob_start();

            if (file_exists($__path))    {
                if ($__vars !== null) {
                    \extract($__vars);
                }
                require($__path);

                try {
                    $this->__html = '';
                    while (\ob_get_length()) {
                        $this->__html .= \ob_get_clean();
                    }
                   } catch (\exception $e) {
                    throw new \exception('Output buffer error occurred');
                }

            } else {
                throw new \exception("Could not load view template file \"{$__path}\"");
            }
        }

        public function __tostring() {
            return (string) $this->__html;
        }

        public function __get($k) {
            if (isset($this->__vars[$k])) {
                return $this->__vars[$k];
            }
        }

        protected function variables() {
            return \array_keys($this->__vars);
        }
    }

