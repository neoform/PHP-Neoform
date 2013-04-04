<?php

	class render_dialog {

		protected $vars;
		protected $path;

		const JS_EXT = 'js';

		public function __construct($path, $preload_vars=null) {
		    $this->path = $path;

			if (is_array($preload_vars)) {
				$this->vars = $preload_vars;
			} else {
				$this->vars = [];
			}

			$this->vars['_ref'] = core::http()->get_ref();
		}

		public function render() {
			core::output()->output_type('json')->body(json_encode($this->vars));
		}

		public function __tostring() {
			return $this->execute();
		}

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

		public function title($v) {
		    $this->vars['content']['title'] = $v;
            return $this;
		}

		public function content($name, array $vars = []) {
		    $html = new render_dialog_view($this->path . '/' . $name, $vars);
		    $this->vars['content'][$name] = (string) $html;
		    return $this;
		}

		public function callback($name) {
		    $js_view = core::path('application') . '/dialogs/' . $this->path . '/' . $name . '.' . self::JS_EXT;

		    if (file_exists($js_view))	{
				try {
    				$this->vars['callbacks'][$name] = file_get_contents($js_view);
	            } catch (Exception $e) {
    				throw new Exception('Error occured while reading JS file');
    			}
            }

		    return $this;
		}
	}

