<?php

    /**
     * Instance of a config
     */
    class config_instance extends ArrayObject {

        use core_instance;

		protected $vars;

		public function __construct($file=null) {
			$this->exchangeArray(
				config_dao::get($file !== null ? '/' . $file : '')
			);
		}

		public function __get($key) {
			return isset($this[$key]) ? $this[$key] : null;
		}

		public function overload(array $overload) {
			$this->exchangeArray(
				array_replace_recursive((array) $this, $overload)
			);
		}
	}