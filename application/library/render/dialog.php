<?php

    class render_dialog {

        protected $vars;
        protected $path;

        const JS_EXT   = 'js.phtml';

        public function __construct($path, $preload_vars=null) {
            $this->path = $path;

            if (is_array($preload_vars)) {
                $this->vars = $preload_vars;
            } else {
                $this->vars = [];
            }

            $this->vars['_ref'] = core::http()->get_ref();
        }

        /**
         * Render dialog and send output to buffer
         */
        public function render() {
            core::output()->output_type('json')->body(json_encode($this->vars));
        }

        /**
         * Save render to string
         *
         * @return string
         */
        public function __tostring() {
            return (string) $this->execute();
        }

        /**
         * Apply custom CSS to the dialog box
         *
         * @param string      $k
         * @param string|null $v
         *
         * @return render_dialog
         */
        public function css($k, $v=null) {
            if (! isset($this->vars['css'])) {
                $this->vars['css'] = [];
            }

            if (is_array($k)) {
                $this->vars['css'] += $k;
            } else {
                $this->vars['css'][$k] = $v;
            }
            return $this;
        }

        /**
         * Set the title of the dialog box
         *
         * @param string $v
         *
         * @return render_dialog
         */
        public function title($v) {
            $this->vars['content']['title'] = $v;
            return $this;
        }

        /**
         * Assign content to a given part of the dialog box
         *
         * @param string $name (eg, title, body, foot)
         * @param array  $vars to be passed to the view
         *
         * @return render_dialog
         */
        public function content($name, array $vars = []) {
            $this->vars['content'][$name] = (string) (new render_dialog_view($this->path . '/' . $name, $vars));
            return $this;
        }

        /**
         * Run JS code callbacks in the dialog
         *
         * @param string $name callback event name
         * @param array $params to be passed to the view
         *
         * @return render_dialog
         * @throws Exception
         */
        public function callback($name, array $params = null) {
            if ($params) {
                $this->vars['callbacks'][$name] = (string) (new render_dialog_view($this->path . '/' . $name, $params));
            } else {
                $js_view = core::path('application') . "/dialogs/{$this->path}/{$name}." . self::JS_EXT;
                if (file_exists($js_view))    {
                    try {
                        $this->vars['callbacks'][$name] = 'function() {' . file_get_contents($js_view) . '}';
                    } catch (Exception $e) {
                        throw new Exception('Error occured while reading JS file');
                    }
                }
            }

            return $this;
        }
    }

